<?php

declare(strict_types=1);

function get_db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dbPath = __DIR__ . '/../data/mbg.sqlite';
    $dsn = 'sqlite:' . $dbPath;

    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');

    return $pdo;
}

function initialize_schema(PDO $db): void
{
    $db->exec(
        'CREATE TABLE IF NOT EXISTS materials (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            unit TEXT NOT NULL,
            current_stock REAL NOT NULL DEFAULT 0,
            min_stock REAL NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $db->exec(
        'CREATE TABLE IF NOT EXISTS stock_movements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            material_id INTEGER NOT NULL,
            txn_date TEXT NOT NULL,
            type TEXT NOT NULL CHECK(type IN ("masuk", "keluar", "pakai")),
            quantity REAL NOT NULL,
            unit_price REAL NOT NULL DEFAULT 0,
            notes TEXT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(material_id) REFERENCES materials(id) ON DELETE CASCADE
        )'
    );

    $db->exec(
        'CREATE TABLE IF NOT EXISTS finance_transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            txn_date TEXT NOT NULL,
            type TEXT NOT NULL CHECK(type IN ("pemasukan", "pengeluaran")),
            category TEXT NOT NULL,
            amount REAL NOT NULL,
            description TEXT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );
}
