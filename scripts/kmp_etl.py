"""
КМП (nobel.provisor.kz) — ETL скрипт
Забирает «БыстрыйОтчёт» с портала nobel.provisor.kz (логин + генерация отчёта +
скачивание — без ручного клика), чистит/агрегирует данные тем же способом, что
и ручной Jupyter-ноутбук (KMP_2026_fixed_v6), и заливает в MySQL (nobeldb.KMP_data,
view `kmp` смотрит поверх неё).

Раньше (ручной процесс) было 3 ручных шага перед заливкой:
  1. Экспорт БыстрыйОтчёт с портала.
  2. Ручная корректировка группы OTC-1/2/3/mix (лист "Группы") и пустых
     "Региональный менеджер" — по данным из нашей системы.
  3. Добавление в общий годовой файл (нужно было, потому что скрипт удалял и
     перезаливал ВЕСЬ год целиком).
Все три шага убраны:
  1. Портал дёргается напрямую (POST-логин без CSRF, см. ниже).
  2. Группа и РМ подтягиваются из основной БД Laravel (employee_kmp_names +
     territories.team / иерархия территорий) — там это уже есть.
  3. Заливка теперь — DELETE+INSERT только по фактическому диапазону дат
     выгрузки, а не по всему году — повторные/частичные прогоны безопасны.

Использование:
  python kmp_etl.py                              # авто-диапазон: 1-е число
                                                  # прошлого месяца -> сегодня
  python kmp_etl.py --from=2026-09-01 --to=2026-10-21   # произвольный диапазон
                                                  # (ручной прогон/перезаливка)

Все пароли/креды — ТОЛЬКО через переменные окружения (их задаёт
app/Console/Commands/RunKmpEtl.php), в коде никогда не хранятся:
  KMP_PORTAL_USERNAME / KMP_PORTAL_PASSWORD   — логин на nobel.provisor.kz
  KMP_NOBELDB_USER / KMP_NOBELDB_PASS         — nobeldb (запись, не read-only)
  KMP_MAIN_DB_HOST / _PORT / _NAME / _USER / _PASS — основная БД Laravel
  KMP_SW_REFERENCE_FILE (необязательно) — путь к личному файлу "Недели
      поставщика.xlsx" (в репозиторий не кладём). Если не задан/файл
      недоступен — колонка SW просто не считается, на остальной пайплайн
      не влияет.
"""

import io
import os
import sys
from datetime import date, datetime

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')
if hasattr(sys.stderr, 'reconfigure'):
    sys.stderr.reconfigure(encoding='utf-8', errors='replace')

import numpy as np
import pandas as pd
import requests
from sqlalchemy import create_engine, text

# ============================================================
# НАСТРОЙКИ ПОДКЛЮЧЕНИЯ (значения — из окружения, см. докстринг выше)
# ============================================================
PORTAL_URL      = "https://nobel.provisor.kz"
PORTAL_USERNAME = os.environ.get("KMP_PORTAL_USERNAME")
PORTAL_PASSWORD = os.environ.get("KMP_PORTAL_PASSWORD")
# Портал проверяет похожесть на браузер — без этого аутентификационная
# cookie иногда сразу же аннулируется сервером при следующем запросе.
PORTAL_HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
                  "(KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    "Referer": PORTAL_URL + "/Admin/FastReports",
}

NOBEL_DB_USER     = os.environ.get("KMP_NOBELDB_USER")
NOBEL_DB_PASSWORD = os.environ.get("KMP_NOBELDB_PASS")
NOBEL_DB_HOST     = "192.168.33.39"
NOBEL_DB_PORT     = "3306"
NOBEL_DB_NAME     = "nobeldb"
NOBEL_TABLE       = "KMP_data"

MAIN_DB_HOST = os.environ.get("KMP_MAIN_DB_HOST")
MAIN_DB_PORT = os.environ.get("KMP_MAIN_DB_PORT", "3306")
MAIN_DB_NAME = os.environ.get("KMP_MAIN_DB_NAME")
MAIN_DB_USER = os.environ.get("KMP_MAIN_DB_USER")
MAIN_DB_PASS = os.environ.get("KMP_MAIN_DB_PASS")

# Справочник "недели поставщика" (SW) — личный рабочий файл, в репозиторий
# не кладём (остаётся только на компьютере, где он уже есть). Если путь не
# задан/файл недоступен — колонку SW просто не считаем (см. process()),
# на остальной пайплайн это не влияет: сейчас SW нигде в приложении не
# используется (ни в KmpController, ни в экспорте).
SW_REFERENCE_FILE = os.environ.get("KMP_SW_REFERENCE_FILE")

LOG_FILE = "storage/logs/kmp_etl.log"

# Группа в системе (territories.team) -> что должно стоять в "Бизнес-подразделение"
# выгрузки КМП. На портале все OTC-группы приходят одной общей "OTC" — тут их
# разводим по факту; RX-группы (1-4) портал и так шлёт правильно, но на всякий
# случай переводим и их тоже (единое правило, без исключений).
TEAM_TO_KMP_GROUP = {
    "OTC-1 GROUP":   "OTC-1",
    "OTC-2 GROUP":   "OTC-2",
    "OTC-3 GROUP":   "OTC-3",
    "OTC-MIX GROUP": "OTC-mix",
    "MSS":           "MSS",
    "1 GROUP":       "1 группа",
    "2 GROUP":       "2 группа",
    "3 GROUP":       "3 группа",
    "4 GROUP":       "4 группа",
}

DROP_COLUMNS = [
    'Вход в аптеку (дата и время)', 'Вход в аптеку (широта и долгота)', 'Юр лицо',
    'Категория аптеки', 'Локусные/Не локусные',
    'Снятие остатков #1 (дата и время)', 'Снятие остатков N (дата и время)',
    'Снятие остатков #1 (широта и долгота)', 'Снятие остатков N (широта и долгота)',
    'Длительность снятия остатков',
    'Подтверждение заказа #1 (дата и время)', 'Подтверждение заказа N (дата и время)',
    'Подтверждение заказа #1 (широта и долгота)', 'Подтверждение заказа N (широта и долгота)',
    'Длительность подтверждения заказа', 'Общая длительность визита',
    'Остаток (упаковки)', 'Заказ (упаковки) - остаток', 'Номер счет-фактуры',
]

NUMERIC_COLUMNS = [
    'Доставленное кол-во', 'Доставленная скидка', 'Доставленная цена',
    'Доставленная сумма без скидки', 'Доставленная сумма со скидкой',
]

GROUP_BY_COLUMNS = [
    'Дата', 'Месяц', 'Год', 'Медпредставитель', 'Региональный менеджер', 'Город',
    'Название аптеки', 'ID аптеки', 'Город аптеки', 'Адрес аптеки', 'БИН аптеки',
    'Дистрибьютор', 'Брэнд', 'Бизнес-подразделение', 'Номер заказа Pharmcenter',
    'SKU_splitted', 'Статус заказа',
]


def log(message: str):
    ts = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    print(f"{ts} [INFO] {message}", flush=True)


# ============================================================
# ДИАПАЗОН ДАТ
# ============================================================
def _parse_date_override():
    date_from = date_to = None
    for arg in sys.argv[1:]:
        if arg.startswith("--from="):
            date_from = datetime.strptime(arg.split("=", 1)[1], "%Y-%m-%d").date()
        elif arg.startswith("--to="):
            date_to = datetime.strptime(arg.split("=", 1)[1], "%Y-%m-%d").date()
    return date_from, date_to


def resolve_date_range():
    override_from, override_to = _parse_date_override()
    if override_from or override_to:
        if not (override_from and override_to):
            raise ValueError("--from и --to нужно задавать вместе")
        return override_from, override_to

    today = date.today()
    first_of_this_month = today.replace(day=1)
    if first_of_this_month.month == 1:
        prev_month_start = first_of_this_month.replace(year=first_of_this_month.year - 1, month=12)
    else:
        prev_month_start = first_of_this_month.replace(month=first_of_this_month.month - 1)
    return prev_month_start, today


# ============================================================
# ПОРТАЛ nobel.provisor.kz
# ============================================================
def fetch_fast_report(date_from: date, date_to: date) -> pd.DataFrame:
    if not PORTAL_USERNAME or not PORTAL_PASSWORD:
        raise RuntimeError("KMP_PORTAL_USERNAME/KMP_PORTAL_PASSWORD не заданы в окружении")

    session = requests.Session()
    session.headers.update(PORTAL_HEADERS)

    log(f"Авторизация на портале КМП ({PORTAL_URL})...")
    resp = session.post(
        f"{PORTAL_URL}/Account/LogOn",
        params={"ReturnUrl": "/Admin/FastReports"},
        data={"UserName": PORTAL_USERNAME, "Password": PORTAL_PASSWORD, "RememberMe": "false"},
        timeout=60,
        allow_redirects=False,
    )
    if resp.status_code not in (302, 200) or ".ASPXAUTH" not in session.cookies.get_dict():
        raise RuntimeError(f"Не удалось авторизоваться на портале КМП (код {resp.status_code})")

    params = {
        "fromDate": date_from.strftime("%d.%m.%Y"),
        "toDate": date_to.strftime("%d.%m.%Y"),
        "typePharmacy": "",
        "typeReport": "0",
        "dropdownUsers": None,
        "deliveredOrders": "0",
        "dropdownGoods": None,
        "distributorFilials": None,
        "distributorsID": None,
        "dropdownPharmacies": None,
    }
    log(f"Запрос БыстрыйОтчёт за {date_from} .. {date_to}...")
    resp = session.post(
        f"{PORTAL_URL}/Admin/GenerateReportReady",
        json=params,
        timeout=600,
    )
    resp.raise_for_status()
    data = resp.json()
    if data.get("FileName") != "БыстрыйОтчёт.xlsx" or not data.get("FileGuid"):
        raise RuntimeError(f"Портал КМП вернул неожиданный ответ: {data}")

    log("Скачивание файла отчёта...")
    resp = session.get(
        f"{PORTAL_URL}/Admin/ReportDownload",
        params={"fileGuid": data["FileGuid"], "filename": data["FileName"]},
        timeout=300,
    )
    resp.raise_for_status()
    log(f"Файл получен, {len(resp.content)} байт.")

    file_bytes = io.BytesIO(resp.content)
    xl = pd.ExcelFile(file_bytes, engine="openpyxl")
    return pd.read_excel(file_bytes, sheet_name=xl.sheet_names[0], header=1, engine="openpyxl")


# ============================================================
# СПРАВОЧНИК «ГРУППА / РМ» ИЗ ОСНОВНОЙ БД LARAVEL
# ============================================================
def load_team_and_manager_lookup() -> dict:
    """kmp_employee_name -> {'group': str|None, 'manager': str|None}"""
    if not all([MAIN_DB_HOST, MAIN_DB_NAME, MAIN_DB_USER, MAIN_DB_PASS]):
        raise RuntimeError("KMP_MAIN_DB_* не заданы в окружении")

    engine = create_engine(
        f"mysql+pymysql://{MAIN_DB_USER}:{MAIN_DB_PASS}@{MAIN_DB_HOST}:{MAIN_DB_PORT}/{MAIN_DB_NAME}?charset=utf8mb4"
    )
    query = text("""
        SELECT
            ekn.kmp_employee_name,
            t.team AS raw_team,
            mgr_emp.full_name AS manager_name
        FROM employee_kmp_names ekn
        JOIN employees e ON e.id = ekn.employee_id
        LEFT JOIN (
            SELECT et.employee_id, et.territory_id,
                   ROW_NUMBER() OVER (PARTITION BY et.employee_id ORDER BY et.assigned_at DESC, et.id DESC) rn
            FROM employee_territory et
        ) cur ON cur.employee_id = e.id AND cur.rn = 1
        LEFT JOIN territories t ON t.id = cur.territory_id
        LEFT JOIN (
            SELECT et2.territory_id, et2.employee_id,
                   ROW_NUMBER() OVER (PARTITION BY et2.territory_id ORDER BY et2.assigned_at DESC, et2.id DESC) rn
            FROM employee_territory et2
        ) mgr ON mgr.territory_id = t.parent_territory_id AND mgr.rn = 1
        LEFT JOIN employees mgr_emp ON mgr_emp.id = mgr.employee_id
    """)
    with engine.connect() as conn:
        rows = conn.execute(query).mappings().all()

    lookup = {}
    for row in rows:
        raw_team = (row["raw_team"] or "").strip()
        lookup[row["kmp_employee_name"]] = {
            "group": TEAM_TO_KMP_GROUP.get(raw_team),
            "manager": row["manager_name"],
        }
    log(f"Справочник групп/РМ: {len(lookup)} сотрудников с привязкой КМП.")
    return lookup


# ============================================================
# ОБРАБОТКА (порт логики KMP_2026_fixed_v6.ipynb)
# ============================================================
def process(df: pd.DataFrame, lookup: dict) -> pd.DataFrame:
    dt = df.drop(columns=[c for c in DROP_COLUMNS if c in df.columns])

    dt[NUMERIC_COLUMNS] = dt[NUMERIC_COLUMNS].replace('-', 0)
    for col in NUMERIC_COLUMNS:
        dt[col] = dt[col].apply(lambda v: float(str(v).replace(',', '.')))

    dt["Date"] = pd.to_datetime(dt["Дата"], dayfirst=True)
    dt["Полное торговое наименование"] = dt["Полное торговое наименование"].str.strip()

    # Группа и РМ — из нашей системы (employee_kmp_names + иерархия территорий),
    # вместо ручного листа "Группы" / ручного заполнения пустых РМ.
    unmatched = set()

    def resolved_group(row):
        info = lookup.get(row["Медпредставитель"])
        if info and info["group"]:
            return info["group"]
        if row["Медпредставитель"] not in lookup:
            unmatched.add(row["Медпредставитель"])
        return row["Бизнес-подразделение"]

    def resolved_manager(row):
        current = row["Региональный менеджер"]
        if isinstance(current, str) and current.strip():
            return current
        info = lookup.get(row["Медпредставитель"])
        return info["manager"] if info and info["manager"] else current

    dt["Бизнес-подразделение"] = dt.apply(resolved_group, axis=1)
    dt["Региональный менеджер"] = dt.apply(resolved_manager, axis=1)

    if unmatched:
        log(f"ВНИМАНИЕ: {len(unmatched)} медпредставителей без привязки КМП в системе "
            f"(группа/РМ не подставлены, оставлено как в выгрузке): {sorted(unmatched)[:20]}"
            + (" ..." if len(unmatched) > 20 else ""))

    dt['Дистрибьютор'] = dt['Дистрибьютор'].fillna('Неизвестен')
    dt['Брэнд'] = dt['Брэнд'].fillna('Неизвестен')
    dt['Региональный менеджер'] = dt['Региональный менеджер'].fillna('Неизвестен')
    dt['Бизнес-подразделение'] = dt['Бизнес-подразделение'].fillna('Неизвестен')
    dt['Номер заказа Pharmcenter'] = dt['Номер заказа Pharmcenter'].fillna(0)

    dt["SKU_splitted"] = dt["Полное торговое наименование"].str.rsplit('/', n=1).apply(lambda x: x[0])

    dt['БИН аптеки'] = dt['БИН аптеки'].astype(str)
    d_a = dt.groupby(GROUP_BY_COLUMNS).agg(
        Цена_KZT=('Цена (KZT)', 'max'),
        Размер_скидки=('Размер скидки, %', 'max'),
        Заказ_упаковки=('Заказ (упаковки) - заказ', 'sum'),
        Дост_скидка=('Доставленная скидка', 'max'),
        Дост_цена=('Доставленная цена', 'max'),
        Дост_колво=('Доставленное кол-во', 'sum'),
        Дост_сумма_скид=('Доставленная сумма со скидкой', 'sum'),
    ).reset_index()
    d_a["Дата"] = pd.to_datetime(d_a["Дата"], dayfirst=True)

    d_a['Department'] = d_a['Бизнес-подразделение'].apply(lambda x: 'OTC' if 'OTC' in str(x) else 'Rx')
    d_a = d_a.reset_index()

    if SW_REFERENCE_FILE and os.path.isfile(SW_REFERENCE_FILE):
        sw = pd.read_excel(SW_REFERENCE_FILE, sheet_name="Sheet1")
        sw = sw.drop_duplicates(subset=['Дата начала', 'Дата окончания', 'Департамент'])
        merged = pd.merge(d_a, sw, left_on='Department', right_on='Департамент', how='left')
        merged['is_between'] = (merged['Дата'] >= merged['Дата начала']) & (merged['Дата'] <= merged['Дата окончания'])
        merged['is_within_KMP'] = (merged['Дата'] >= merged['КМП ДН']) & (merged['Дата'] <= merged['КМП ДО'])
        merged['SW'] = ''
        merged.loc[merged['is_between'] & merged['is_within_KMP'], 'SW'] = 'SW last 2 days'
        merged.loc[merged['is_between'] & ~merged['is_within_KMP'], 'SW'] = 'SW'
        merged.loc[~merged['is_between'], 'SW'] = 'NOT SW'
        merged = merged[merged['SW'] != 'NOT SW'][['index', 'SW']]
        d_c = pd.merge(d_a, merged, on='index', how='left')
    else:
        log("KMP_SW_REFERENCE_FILE не задан/недоступен — колонка SW не рассчитывается.")
        d_c = d_a.copy()
        d_c['SW'] = None

    d_c['Distributor'] = d_c['Дистрибьютор'].apply(lambda x: str(x).split(' ', 1)[0])
    d_c = d_c.rename(columns={'Дистрибьютор': 'Distributor_branch'})

    d_c['_disc2'] = np.where(d_c['Distributor'] == 'Зерде', d_c['Размер_скидки'] + d_c['Дост_скидка'], d_c['Дост_скидка'])
    d_c['_disc3'] = np.where(d_c['Размер_скидки'] >= d_c['_disc2'], d_c['Размер_скидки'], d_c['_disc2'])
    d_c['_disc4'] = ((d_c['Дост_цена'] / d_c['Цена_KZT'] - 1) * (-100)).round(2)
    d_c['_disc5'] = np.where(d_c['_disc3'] > d_c['_disc4'], d_c['_disc3'], d_c['_disc4'])
    d_c['Дост_скидка'] = d_c['_disc5']
    d_c = d_c.drop(columns=['_disc2', '_disc3', '_disc4', '_disc5'])

    d_c['Региональный менеджер'] = d_c['Региональный менеджер'].astype(str).str.replace('РМ', '', regex=False).str.strip()

    d_c['Price'] = np.where(d_c['Цена_KZT'] == 0, d_c['Дост_цена'], d_c['Цена_KZT'])
    d_c['Amount'] = d_c['Дост_сумма_скид'] * 100 / (100 - d_c['Дост_скидка'])
    d_c['Discount_tot'] = d_c['Дост_скидка']
    d_c['Amount_disc'] = d_c['Amount'] * (100 - d_c['Размер_скидки']) / 100
    d_c['Amount_disc_tot'] = d_c['Дост_сумма_скид']

    return d_c.drop(columns=['index'])


# ============================================================
# ЗАЛИВКА В MYSQL
# ============================================================
def save_to_mysql(d_c: pd.DataFrame, date_from: date, date_to: date):
    if not NOBEL_DB_USER or not NOBEL_DB_PASSWORD:
        raise RuntimeError("KMP_NOBELDB_USER/KMP_NOBELDB_PASS не заданы в окружении")

    engine = create_engine(
        f"mysql+pymysql://{NOBEL_DB_USER}:{NOBEL_DB_PASSWORD}@{NOBEL_DB_HOST}:{NOBEL_DB_PORT}/{NOBEL_DB_NAME}?charset=utf8mb4"
    )

    def mysql_type(dtype):
        s = str(dtype)
        if 'int' in s: return 'BIGINT'
        if 'float' in s: return 'DOUBLE'
        if 'datetime' in s: return 'DATETIME'
        if 'bool' in s: return 'TINYINT(1)'
        return 'TEXT'

    with engine.begin() as conn:
        exists = conn.execute(text("""
            SELECT COUNT(*) FROM information_schema.tables
            WHERE table_schema = :db AND table_name = :t
        """), {"db": NOBEL_DB_NAME, "t": NOBEL_TABLE}).scalar()

        if not exists:
            columns_def = ", ".join(f"`{c}` {mysql_type(d_c[c].dtype)}" for c in d_c.columns)
            conn.execute(text(
                f"CREATE TABLE `{NOBEL_TABLE}` ({columns_def}) "
                f"CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            ))
            log(f"Таблица `{NOBEL_TABLE}` создана.")

        deleted = conn.execute(text(
            f"DELETE FROM `{NOBEL_TABLE}` WHERE `Дата` >= :from_dt AND `Дата` < :to_dt"
        ), {"from_dt": date_from.isoformat(), "to_dt": (date_to + pd.Timedelta(days=1)).isoformat()})
        log(f"Удалено старых строк за {date_from} .. {date_to}: {deleted.rowcount}")

        d_c.to_sql(NOBEL_TABLE, conn, if_exists="append", index=False, chunksize=1000)
        log(f"Залито строк: {len(d_c)}")


def main():
    date_from, date_to = resolve_date_range()
    log(f"Запуск ETL КМП: {date_from} .. {date_to}")

    df = fetch_fast_report(date_from, date_to)
    log(f"Получено строк из БыстрыйОтчёт: {len(df)}")

    if df.empty:
        log("Пустая выгрузка за этот период — нечего заливать.")
        return

    lookup = load_team_and_manager_lookup()
    d_c = process(df, lookup)
    log(f"После агрегации: {len(d_c)} строк.")

    save_to_mysql(d_c, date_from, date_to)
    log("ETL КМП успешно завершён.")


if __name__ == "__main__":
    try:
        main()
    except Exception as e:
        log(f"ОШИБКА: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)
