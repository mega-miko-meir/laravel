"""
Nobel CRM — ETL скрипт
Загружает отчёты из API Nobel CRM в MySQL (nobeldb.stg_nobel_report_N).

Для помесячных отчётов (Визиты/Бренды/CLM-презентации, №3/4/5) целевой месяц
определяется автоматически на момент запуска:
  - 1-е число месяца -> догружается ПРЕДЫДУЩИЙ месяц целиком (в CRM ещё могут
    дозаписываться визиты за последние дни только что закрывшегося месяца —
    это последний шанс забрать их все);
  - остальные дни -> текущий месяц.
Загрузка для этих отчётов — дозапись: старые строки ЗА ЭТОТ МЕСЯЦ удаляются,
затем пишутся свежие (история прошлых месяцев в таблице не теряется). Старая
версия скрипта всегда полностью перезаписывала таблицу фиксированным
диапазоном дат (жёстко прописанным в коде) — поэтому новые данные никогда
не подхватывались, пока диапазон не правили руками.

Использование:
  python nobel_etl.py              # отчёты по умолчанию (см. REPORTS_TO_RUN)
  python nobel_etl.py 2            # только отчёт №2
  python nobel_etl.py 3 4 5        # отчёты 3, 4 и 5
  python nobel_etl.py --month=2026-09       # досылка конкретного месяца (не
                                             # дожидаясь, пока он снова станет
                                             # "текущим" или "предыдущим") —
                                             # для случаев, когда часть данных
                                             # синхронизировалась в CRM с
                                             # опозданием и не попала в обычный
                                             # прогон своего месяца
  python nobel_etl.py --month=2026-09 3 4 5 # то же самое, но только отчёты 3,4,5
"""

import io
import logging
import sys
from datetime import date, datetime, timedelta

# Windows CP1251 консоль не поддерживает Unicode-символы — принудительно UTF-8
if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')
if hasattr(sys.stderr, 'reconfigure'):
    sys.stderr.reconfigure(encoding='utf-8', errors='replace')

import requests
import pandas as pd
from sqlalchemy import create_engine, text

# ============================================================
# ОТЧЁТЫ ПО УМОЛЧАНИЮ (когда скрипт запущен без номеров в аргументах,
# как это делает ежедневное расписание — см. routes/console.php)
# ============================================================
REPORTS_TO_RUN = [1, 2, 3, 4, 5]

# ============================================================
# НАСТРОЙКИ ПОДКЛЮЧЕНИЯ
# ============================================================
API_URL        = "https://nobel.qs-crm.com:1331"
TOKEN_ENDPOINT = "/reportAPI/login"
CSV_ENDPOINT   = "/reportAPI/reportCSV"
JSON_ENDPOINT  = "/reportAPI/report"
USERNAME       = "akimbekov"
PASSWORD       = "ak9933"

DB_USER        = "userdb"
DB_PASSWORD    = "!!Shadow2023"
DB_HOST        = "192.168.33.39"
DB_PORT        = "3306"
DB_NAME        = "nobeldb"

LOG_FILE       = "storage/logs/nobel_etl.log"

# ============================================================
# ЦЕЛЕВОЙ МЕСЯЦ ДЛЯ ПОМЕСЯЧНЫХ ОТЧЁТОВ (3, 4, 5) — см. докстринг выше.
# --month=YYYY-MM в аргументах командной строки переопределяет автоматику
# (используется кнопкой "Обновить данные CRM" в интерфейсе, когда нужно
# досылать конкретный прошедший месяц, а не только текущий/предыдущий).
# ============================================================
def _parse_month_override():
    for arg in sys.argv[1:]:
        if arg.startswith("--month="):
            value = arg.split("=", 1)[1]
            try:
                y, m = value.split("-")
                return int(y), int(m)
            except ValueError:
                raise SystemExit(f"Неверный формат --month: {value!r}, ожидается YYYY-MM")
    return None


_override = _parse_month_override()
if _override:
    TARGET_YEAR, TARGET_MONTH = _override
else:
    _now = datetime.now()
    if _now.day == 1:
        _target = _now.replace(day=1) - timedelta(days=1)
    else:
        _target = _now
    TARGET_YEAR, TARGET_MONTH = _target.year, _target.month

# ============================================================
# КОНФИГУРАЦИЯ ОТЧЁТОВ
# ============================================================
REPORTS = {
    1: {
        "number":       1,
        "name":         "ЛПУ / Аптеки",
        "table":        "stg_nobel_report_1",
        "endpoint":     "csv",
        "is_monthly":   False,
        "filters":      None,
        "int_cols":     [],
        "date_cols":    [],
        "datetime_cols":[],
        "json_fields":  [],
    },
    2: {
        "number":       2,
        "name":         "Врачи",
        "table":        "stg_nobel_report_2",
        "endpoint":     "csv",
        "is_monthly":   False,
        "filters":      None,
        "int_cols":     [
            "organization_etalon_id", "organization_id",
            "customer_id", "customer_etalon_id",
            "customer_project", "customer_birth_date",
            "customer_mobil_phone",
        ],
        "date_cols":    [
            "customer_spesiality_group", "customer_target",
            "last_appointment_date",
        ],
        "datetime_cols":[],
        "json_fields":  [],
    },
    3: {
        "number":       3,
        "name":         "Визиты",
        "table":        "stg_nobel_report_3",
        "endpoint":     "csv",
        "is_monthly":   True,
        "target_year":  TARGET_YEAR,
        "target_month": TARGET_MONTH,
        "date_col_for_delete": "appointment_Date",
        "int_cols":     [
            "organization_etalon_id", "organization_id",
            "customer_id", "customer_etalon_id",
            "customer_project", "employee_id",
            "appointment_number_of_visit", "year",
            "appointment_close_after19", "appointment_duration_less5",
            "appointment_duration", "appointment_id",
            "documents_showTime",
        ],
        "date_cols":    [
            "appointment_Date", "appointment_CreateDate",
            "appointment_CloseDate", "double_visit_Date",
        ],
        "datetime_cols":[],
        "json_fields":  [],
    },
    4: {
        "number":       4,
        "name":         "Бренды",
        "table":        "stg_nobel_report_4",
        "endpoint":     "csv",
        "is_monthly":   True,
        "target_year":  TARGET_YEAR,
        "target_month": TARGET_MONTH,
        "date_col_for_delete": "mine_add_date",
        "int_cols":     ["organization_etalon_id", "year"],
        "date_cols":    [],
        "datetime_cols":["mine_add_date"],
        "json_fields":  [],
    },
    5: {
        "number":       5,
        "name":         "CLM-презентации",
        "table":        "stg_nobel_report_5",
        "endpoint":     "json",
        "is_monthly":   True,
        "target_year":  TARGET_YEAR,
        "target_month": TARGET_MONTH,
        # У этого отчёта в принципе нет колонки с датой визита (ни в ответе
        # API, ни в таблице) — "appointment_Date" была угадана и не
        # существует, из-за чего дозапись каждый день молча дублировала все
        # строки, т.к. удалить старые было нечем (см. process_report()/
        # save_to_mysql(): "synthetic_date_col" сам добавляет и наполняет
        # эту колонку константой = месяц загрузки, если её ещё нет).
        "date_col_for_delete": "etl_load_month",
        "synthetic_date_col": True,
        "int_cols":     [
            "appointment_id", "appointment_count",
            "show_count", "show_time",
        ],
        "date_cols":    [],
        "datetime_cols":[],
        "json_fields":  [
            "department", "region", "province", "manager", "employee",
            "appointment_id", "document", "presentation", "slide",
            "appointment_count", "show_count", "show_time", "show_time_avg",
        ],
    },
}


# ============================================================
# ЛОГИРОВАНИЕ
# ============================================================
def setup_logging(log_file: str) -> logging.Logger:
    logger = logging.getLogger("nobel_etl")
    logger.setLevel(logging.INFO)
    if logger.handlers:
        logger.handlers.clear()
    fmt = logging.Formatter(
        "%(asctime)s [%(levelname)s] %(message)s",
        datefmt="%Y-%m-%d %H:%M:%S"
    )
    console = logging.StreamHandler(sys.stdout)
    console.setFormatter(fmt)
    logger.addHandler(console)
    if log_file:
        fh = logging.FileHandler(log_file, encoding="utf-8")
        fh.setFormatter(fmt)
        logger.addHandler(fh)
    return logger


# ============================================================
# АВТОРИЗАЦИЯ
# ============================================================
def get_token(logger):
    logger.info("Авторизация в API...")
    try:
        resp = requests.get(
            f"{API_URL}{TOKEN_ENDPOINT}",
            params={"username": USERNAME, "password": PASSWORD},
            headers={"Accept": "application/json"},
            timeout=300,
        )
        resp.raise_for_status()
        data = resp.json()
    except Exception as e:
        raise RuntimeError(f"Ошибка получения токена: {e}")

    token = data.get("token")
    if not token:
        raise RuntimeError(f"Токен не найден в ответе API: {data}")

    logger.info("Токен успешно получен.")
    return token


# ============================================================
# ФОРМИРОВАНИЕ ТЕЛА ЗАПРОСА
# ============================================================
def build_request_body(cfg):
    body = {"number": cfg["number"]}
    if cfg.get("is_monthly"):
        year = cfg["target_year"]
        month = cfg["target_month"]
        body["filters"] = {
            "yearFrom": year, "monthFrom": month,
            "yearTo":   year, "monthTo":   month,
        }
    elif cfg.get("filters"):
        body["filters"] = cfg["filters"]
    return body


# ============================================================
# ЗАГРУЗКА CSV
# ============================================================
def fetch_csv_report(token, cfg, logger):
    body = build_request_body(cfg)
    headers = {
        "Authorization": f"Bearer {token}",
        "Content-Type":  "application/json",
        "Accept":        "text/csv",
        "User-Agent":    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
    }

    logger.info(f"  Запрос CSV, filters={body.get('filters', 'нет')}...")
    resp = requests.post(f"{API_URL}{CSV_ENDPOINT}", json=body, headers=headers, timeout=1800)
    logger.info(f"  STATUS: {resp.status_code}, LENGTH: {len(resp.content)} bytes")

    if resp.status_code >= 400:
        logger.error(f"Ответ сервера с ошибкой: {resp.text}")
    resp.raise_for_status()

    csv_text = resp.content.decode("utf-8")
    if not csv_text.strip():
        logger.warning("  Сервер вернул пустой CSV-ответ (0 байт).")
        return None

    df = pd.read_csv(io.StringIO(csv_text), sep=None, engine="python", dtype=object)
    logger.info(f"  Получено: {len(df)} строк, {len(df.columns)} колонок.")
    return df


# ============================================================
# ЗАГРУЗКА JSON
# ============================================================
def fetch_json_report(token, cfg, logger):
    body = build_request_body(cfg)
    headers = {
        "Authorization": f"Bearer {token}",
        "Content-Type":  "application/json",
    }

    logger.info(f"  Запрос JSON, filters={body.get('filters', 'нет')}...")
    resp = requests.post(f"{API_URL}{JSON_ENDPOINT}", json=body, headers=headers, timeout=1800)
    if resp.status_code >= 400:
        logger.error(f"Ответ сервера с ошибкой: {resp.text}")
    resp.raise_for_status()

    data = resp.json()
    if not isinstance(data, list):
        raise RuntimeError(f"Ожидался JSON-массив, получено: {type(data)}")

    df = pd.DataFrame(data)
    if cfg["json_fields"]:
        existing = [c for c in cfg["json_fields"] if c in df.columns]
        df = df[existing]

    logger.info(f"  Получено: {len(df)} строк, {len(df.columns)} колонок.")
    return df


# ============================================================
# ПРЕОБРАЗОВАНИЕ ТИПОВ
# ============================================================
def cast_types(df, cfg, logger):
    for col in cfg["int_cols"]:
        if col in df.columns:
            numeric = pd.to_numeric(df[col], errors="coerce")
            df[col] = numeric.where(numeric.isna(), numeric.round()).astype("Int64")
    for col in cfg["date_cols"]:
        if col in df.columns:
            df[col] = pd.to_datetime(df[col], dayfirst=True, errors="coerce").dt.date
    for col in cfg["datetime_cols"]:
        if col in df.columns:
            df[col] = pd.to_datetime(df[col], dayfirst=True, errors="coerce")
    logger.info(
        f"  Типы: int x{len(cfg['int_cols'])}, "
        f"date x{len(cfg['date_cols'])}, "
        f"datetime x{len(cfg['datetime_cols'])}."
    )
    return df


# ============================================================
# СОХРАНЕНИЕ В БД (ЧАСТИЧНОЕ ИЛИ ПОЛНОЕ)
# ============================================================
def save_to_mysql(df, cfg, engine, logger):
    table = cfg["table"]

    if cfg.get("is_monthly") and cfg.get("target_year") and cfg.get("target_month") and cfg.get("date_col_for_delete"):
        year = cfg["target_year"]
        month = cfg["target_month"]
        date_col = cfg["date_col_for_delete"]

        if cfg.get("synthetic_date_col"):
            # Колонки с датой может не быть в таблице вообще (первый запуск
            # после этого фикса, либо таблица создавалась до него) —
            # самовосстанавливаемся, а не падаем молча, как раньше.
            # "ADD COLUMN IF NOT EXISTS" этот сервер MySQL не принимает —
            # проверяем через information_schema вручную.
            try:
                with engine.begin() as conn:
                    col_exists = conn.execute(text("""
                        SELECT COUNT(*) FROM information_schema.columns
                        WHERE table_schema = DATABASE() AND table_name = :t AND column_name = :c
                    """), {"t": table, "c": date_col}).scalar()
                    if not col_exists:
                        conn.execute(text(f"ALTER TABLE `{table}` ADD COLUMN `{date_col}` DATE NULL"))
            except Exception as alter_err:
                logger.warning(f"  Не удалось добавить колонку `{date_col}` в `{table}`: {alter_err}")

        logger.info(f"  Удаление старых данных в `{table}` за {month:02d}.{year} по колонке `{date_col}`...")
        try:
            with engine.begin() as conn:
                delete_sql = text(
                    f"DELETE FROM `{table}` "
                    f"WHERE YEAR(`{date_col}`) = :year AND MONTH(`{date_col}`) = :month"
                )
                result = conn.execute(delete_sql, {"year": year, "month": month})
                logger.info(f"  Удалено старых строк: {result.rowcount}")
        except Exception as del_err:
            logger.warning(f"  Не удалось удалить старые данные (таблица может отсутствовать): {del_err}")

        logger.info(f"  Дозапись новых данных в `{table}` ({len(df)} строк)...")
        df.to_sql(name=table, con=engine, if_exists="append", index=False, chunksize=5000)
    else:
        logger.info(f"  Полная перезапись таблицы `{table}` ({len(df)} строк)...")
        df.to_sql(name=table, con=engine, if_exists="replace", index=False, chunksize=5000)

    logger.info(f"  [OK] Таблица `{table}` обновлена.")


# ============================================================
# ОБРАБОТКА ОДНОГО ОТЧЁТА
# ============================================================
def process_report(report_number, token, engine, logger):
    cfg = REPORTS.get(report_number)
    if cfg is None:
        logger.error(f"Отчёт №{report_number} не найден в конфигурации.")
        return False

    logger.info("=" * 55)
    logger.info(f"Отчёт №{report_number}: {cfg['name']}")
    logger.info("=" * 55)

    try:
        if cfg["endpoint"] == "csv":
            df = fetch_csv_report(token, cfg, logger)
        else:
            df = fetch_json_report(token, cfg, logger)

        if df is not None and not df.empty:
            if cfg.get("synthetic_date_col"):
                # Нет реальной даты в данных — проставляем месяц загрузки
                # константой во все строки, чтобы дозапись могла находить
                # и удалять именно "свою" порцию в следующий раз.
                df[cfg["date_col_for_delete"]] = date(cfg["target_year"], cfg["target_month"], 1)
            df = cast_types(df, cfg, logger)
            save_to_mysql(df, cfg, engine, logger)
        else:
            logger.warning("  Нет данных для сохранения.")

        return True
    except Exception as e:
        logger.error(f"Ошибка при обработке отчёта №{report_number}: {e}", exc_info=True)
        return False


# ============================================================
# ЗАПУСК
# ============================================================
def main(reports_to_run=None):
    logger = setup_logging(LOG_FILE)
    logger.info(f"Запуск ETL: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")

    if not reports_to_run:
        reports_to_run = REPORTS_TO_RUN
    logger.info(f"Отчёты к загрузке: {reports_to_run}")
    if any(REPORTS[n].get("is_monthly") for n in reports_to_run if n in REPORTS):
        logger.info(f"Целевой месяц для помесячных отчётов: {TARGET_MONTH:02d}.{TARGET_YEAR}")

    engine = create_engine(
        f"mysql+pymysql://{DB_USER}:{DB_PASSWORD}"
        f"@{DB_HOST}:{DB_PORT}/{DB_NAME}?charset=utf8mb4"
    )

    try:
        token = get_token(logger)
    except RuntimeError as e:
        logger.critical(str(e))
        sys.exit(1)

    results = {}
    for n in reports_to_run:
        results[n] = process_report(n, token, engine, logger)

    logger.info("=" * 55)
    logger.info("ИТОГ:")
    for n, ok in results.items():
        status = "[OK]" if ok else "[ОШИБКА]"
        logger.info(f"  Отчёт №{n} ({REPORTS[n]['name']}): {status}")

    failed = [n for n, ok in results.items() if not ok]
    if failed:
        sys.exit(1)


if __name__ == "__main__":
    # Аргументы командной строки: python nobel_etl.py 3 4 5
    args = [int(a) for a in sys.argv[1:] if a.isdigit()]
    main(args if args else None)
