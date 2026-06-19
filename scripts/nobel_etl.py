"""
Nobel CRM - ETL скрипт
Загружает отчёты из API Nobel CRM в MySQL.

Использование:
  python nobel_etl.py              # все отчёты
  python nobel_etl.py 2            # только отчёт №2
  python nobel_etl.py 3 4 5        # отчёты 3, 4 и 5
"""

import io
import logging
import sys
from datetime import datetime

# Windows CP1251 консоль не поддерживает Unicode-символы — принудительно UTF-8
if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')
if hasattr(sys.stderr, 'reconfigure'):
    sys.stderr.reconfigure(encoding='utf-8', errors='replace')

import requests
import pandas as pd
from sqlalchemy import create_engine

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
# КОНФИГУРАЦИЯ ОТЧЁТОВ
# ============================================================
REPORTS = {
    1: {
        "number":       1,
        "name":         "ЛПУ / Аптеки",
        "table":        "stg_nobel_report_1",
        "endpoint":     "csv",
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
        "filters":      {"yearFrom": 2026, "monthFrom": 1,
                         "yearTo":   2026, "monthTo":   6},
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
        "filters":      {"yearFrom": 2026, "monthFrom": 1,
                         "yearTo":   2026, "monthTo":   6},
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
        "filters":      {"yearFrom": 2026, "monthFrom": 1,
                         "yearTo":   2026, "monthTo":   6},
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
    8: {
        "number":       8,
        "name":         "Полный список ЛПУ и Аптек (Все)",
        "table":        "stg_nobel_report_8",
        "endpoint":     "csv",
        "filters":      None,
        "int_cols":     [
            "organization_etalon_id", "organization_id",
            "sales_of_year", "sales_by_month", "organization_code_okpo"
        ],
        "date_cols":    [
            "last_actualization_date", "last_visit_date", "mine_add_date"
        ],
        "datetime_cols":[],
        "json_fields":  [],
    },
}


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


def fetch_csv_report(token, cfg, logger):
    body = {"number": cfg["number"]}
    if cfg["filters"]:
        body["filters"] = cfg["filters"]

    headers = {
        "Authorization": f"Bearer {token}",
        "Content-Type":  "application/json",
        "Accept":        "text/csv",
        "User-Agent":    (
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
            "AppleWebKit/537.36 (KHTML, like Gecko) "
            "Chrome/91.0.4472.124 Safari/537.36"
        ),
    }

    logger.info(f"  Запрос CSV, filters={cfg['filters']}...")
    try:
        resp = requests.post(
            f"{API_URL}{CSV_ENDPOINT}",
            json=body,
            headers=headers,
            timeout=1800,
        )
        resp.raise_for_status()
    except Exception as e:
        raise RuntimeError(f"Ошибка запроса CSV-отчёта: {e}")

    df = pd.read_csv(io.StringIO(resp.content.decode("utf-8")),
                     delimiter=",", dtype=object)
    logger.info(f"  Получено: {len(df)} строк, {len(df.columns)} колонок.")
    return df


def fetch_json_report(token, cfg, logger):
    body = {"number": cfg["number"]}
    if cfg["filters"]:
        body["filters"] = cfg["filters"]

    headers = {
        "Authorization": f"Bearer {token}",
        "Content-Type":  "application/json",
    }

    logger.info(f"  Запрос JSON, filters={cfg['filters']}...")
    try:
        resp = requests.post(
            f"{API_URL}{JSON_ENDPOINT}",
            json=body,
            headers=headers,
            timeout=1800,
        )
        resp.raise_for_status()
        data = resp.json()
    except Exception as e:
        raise RuntimeError(f"Ошибка запроса JSON-отчёта: {e}")

    if not isinstance(data, list):
        raise RuntimeError(f"Ожидался JSON-массив, получено: {type(data)}")

    df = pd.DataFrame(data)
    if cfg["json_fields"]:
        existing = [c for c in cfg["json_fields"] if c in df.columns]
        df = df[existing]

    logger.info(f"  Получено: {len(df)} строк, {len(df.columns)} колонок.")
    return df


def cast_types(df, cfg, logger):
    for col in cfg["int_cols"]:
        if col in df.columns:
            numeric = pd.to_numeric(df[col], errors="coerce")
            df[col] = numeric.where(numeric.isna(), numeric.round()).astype("Int64")
    for col in cfg["date_cols"]:
        if col in df.columns:
            df[col] = pd.to_datetime(df[col], dayfirst=True,
                                     errors="coerce").dt.date
    for col in cfg["datetime_cols"]:
        if col in df.columns:
            df[col] = pd.to_datetime(df[col], dayfirst=True, errors="coerce")
    logger.info(
        f"  Типы: int x{len(cfg['int_cols'])}, "
        f"date x{len(cfg['date_cols'])}, "
        f"datetime x{len(cfg['datetime_cols'])}."
    )
    return df


def save_to_mysql(df, table, engine, logger):
    logger.info(f"  Запись в `{table}` ({len(df)} строк)...")
    df.to_sql(name=table, con=engine, if_exists="replace",
              index=False, chunksize=5000)
    logger.info(f"  [OK] Таблица `{table}` обновлена.")


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
        df = cast_types(df, cfg, logger)
        save_to_mysql(df, cfg["table"], engine, logger)
        return True
    except Exception as e:
        logger.error(
            f"Ошибка при обработке отчёта №{report_number}: {e}",
            exc_info=True
        )
        return False


def main(reports_to_run=None):
    logger = setup_logging(LOG_FILE)
    logger.info(f"Запуск ETL: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")

    if not reports_to_run:
        reports_to_run = list(REPORTS.keys())
    logger.info(f"Отчёты к загрузке: {reports_to_run}")

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
