# MBG Inventory API Reference (v1)

Base URL (local):

`http://localhost:8000/api/v1`

Semua response menggunakan format JSON.

## Authentication

Flow autentikasi menggunakan Laravel Sanctum personal access token.

### Login
- Method: `POST`
- URL: `/api/auth/login`
- Body:
```json
{
  "email": "admin@mbg.local",
  "password": "password123"
}
```

### Me
- Method: `GET`
- URL: `/api/auth/me`
- Header: `Authorization: Bearer {token}`

### Logout
- Method: `POST`
- URL: `/api/auth/logout`
- Header: `Authorization: Bearer {token}`

Catatan:
- Semua endpoint `/api/v1/*` sekarang wajib token (`auth:sanctum`).
- Seeder default membuat user admin pusat:
  - email: `admin@mbg.local`
  - password: `password123`

## Health Check

- Method: `GET`
- URL: `/up`
- Catatan: endpoint dari Laravel health route.

## Roles

### List Roles
- Method: `GET`
- URL: `/roles`

## Locations

### List Locations
- Method: `GET`
- URL: `/locations`

### Create Location
- Method: `POST`
- URL: `/locations`
- Body:
```json
{
  "code": "LOC-001",
  "name": "MBG Titik A",
  "address": "Jl. Contoh No. 1",
  "is_active": true
}
```

### Show Location
- Method: `GET`
- URL: `/locations/{id}`

### Update Location
- Method: `PUT` / `PATCH`
- URL: `/locations/{id}`
- Body (parsial diperbolehkan):
```json
{
  "name": "MBG Titik A - Update"
}
```

### Delete Location
- Method: `DELETE`
- URL: `/locations/{id}`

## Items

### List Items
- Method: `GET`
- URL: `/items`

### Create Item
- Method: `POST`
- URL: `/items`
- Body:
```json
{
  "sku": "BRG-001",
  "name": "Beras Premium",
  "unit": "kg",
  "is_active": true
}
```

### Show Item
- Method: `GET`
- URL: `/items/{id}`

### Update Item
- Method: `PUT` / `PATCH`
- URL: `/items/{id}`
- Body (parsial diperbolehkan):
```json
{
  "name": "Beras Premium 5kg"
}
```

### Delete Item
- Method: `DELETE`
- URL: `/items/{id}`

## Goods Receipts (Stok Masuk)

### List Goods Receipts
- Method: `GET`
- URL: `/goods-receipts`

### Create Goods Receipt
- Method: `POST`
- URL: `/goods-receipts`
- Body:
```json
{
  "trx_date": "2026-03-03",
  "location_id": 1,
  "notes": "Penerimaan dari supplier A",
  "lines": [
    {
      "item_id": 1,
      "qty": 25,
      "unit_cost": 12000
    },
    {
      "item_id": 2,
      "qty": 10,
      "unit_cost": 8500
    }
  ]
}
```

### Show Goods Receipt
- Method: `GET`
- URL: `/goods-receipts/{id}`

## Consumptions (Pemakaian Stok)

### List Consumptions
- Method: `GET`
- URL: `/consumptions`

### Create Consumption
- Method: `POST`
- URL: `/consumptions`
- Body:
```json
{
  "trx_date": "2026-03-03",
  "location_id": 1,
  "notes": "Pemakaian harian dapur",
  "lines": [
    {
      "item_id": 1,
      "qty": 5
    }
  ]
}
```

Catatan penting:
- Service akan menolak transaksi jika saldo stok tidak cukup.

### Show Consumption
- Method: `GET`
- URL: `/consumptions/{id}`

## Stocks

### Get Stock Balances (Ledger Aggregation)
- Method: `GET`
- URL: `/stocks/balances`
- Query optional:
  - `location_id`
  - `item_id`

Contoh:
- `/stocks/balances?location_id=1`
- `/stocks/balances?location_id=1&item_id=2`

### Get Low Stock Alerts
- Method: `GET`
- URL: `/stocks/low?location_id={id}`
- Catatan: menampilkan item dengan `balance <= min_qty`.

### Set/Upsert Stock Minimum
- Method: `POST`
- URL: `/stocks/minimums`
- Body:
```json
{
  "location_id": 1,
  "item_id": 2,
  "min_qty": 15
}
```

## Error Format (Umum)

Validation error (`422`) akan mengikuti format default Laravel:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "Error message"
    ]
  }
}
```

## Next Suggested Endpoint

Untuk frontend dashboard, endpoint berikut direkomendasikan sebagai langkah lanjut:
- Rekap pemasukan/pemakaian per periode per lokasi
- Top item paling banyak dipakai
- Nilai persediaan per lokasi
