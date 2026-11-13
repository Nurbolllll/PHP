<?php
declare(strict_types=1);

function normalizeText(string $value): string
{
    $value = trim($value);
    $value = preg_replace("/\s+/u", " ", $value) ?? $value;
    return mb_strtolower($value, "UTF-8");
}

function validateProduct(array $product): void
{
    $required = ["id", "name", "category", "price", "stock", "created_at", "author", "genre"];
    foreach ($required as $key) {
        if (!array_key_exists($key, $product)) {
            throw new InvalidArgumentException("Отсутствует обязательное поле: $key");
        }
    }

    if (!is_int($product["id"]) || $product["id"] <= 0) {
        throw new InvalidArgumentException("Некорректный id");
    }

    if (trim((string) $product["name"]) === "") {
        throw new InvalidArgumentException("Пустое название");
    }

    if (!is_numeric($product["price"]) || (float) $product["price"] < 0) {
        throw new InvalidArgumentException("Некорректная цена");
    }

    if (!is_int($product["stock"]) || $product["stock"] < 0) {
        throw new InvalidArgumentException("Некорректный остаток");
    }

    $date = DateTimeImmutable::createFromFormat("!Y-m-d", (string) $product["created_at"]);
    if ($date === false || $date->format("Y-m-d") !== $product["created_at"]) {
        throw new InvalidArgumentException("Некорректная дата");
    }
}

function searchByAuthor(array $items, string $authorQuery): array
{
    $authorQuery = normalizeText($authorQuery);
    if ($authorQuery === "") return $items;

    return array_values(array_filter($items, fn(array $p): bool =>
        mb_stripos(normalizeText((string) $p["author"]), $authorQuery, 0, "UTF-8") !== false
    ));
}

function filterCatalog(array $items, ?string $genre = null, ?float $maxPrice = null, bool $onlyAvailable = false): array
{
    $genre = $genre === null ? null : normalizeText($genre);

    return array_values(array_filter($items, function (array $p) use ($genre, $maxPrice, $onlyAvailable): bool {
        if ($genre !== null && normalizeText((string) $p["genre"]) !== $genre) {
            return false;
        }
        if ($maxPrice !== null && (float) $p["price"] > $maxPrice) {
            return false;
        }
        if ($onlyAvailable && $p["stock"] <= 0) {
            return false;
        }
        return true;
    }));
}

function sortByName(array $items, bool $ascending = true): array
{
    usort($items, fn(array $a, array $b): int => $ascending
        ? strcasecmp((string)$a["name"], (string)$b["name"])
        : strcasecmp((string)$b["name"], (string)$a["name"])
    );
    return $items;
}

function inventoryValue(array $items): float
{
    return round(array_reduce($items, fn(float $sum, array $p): float =>
        $sum + (float) $p["price"] * (int) $p["stock"], 0.0), 2);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function renderTable(array $items): string
{
    $html = "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
    $html .= "<tr><th>ID</th><th>Название</th><th>Автор</th><th>Жанр</th><th>Категория</th><th>Цена</th><th>Остаток</th></tr>";
    
    foreach ($items as $p) {
        $html .= "<tr>";
        $html .= "<td>" . (int) $p["id"] . "</td>";
        $html .= "<td>" . e((string) $p["name"]) . "</td>";
        $html .= "<td>" . e((string) $p["author"]) . "</td>";
        $html .= "<td>" . e((string) $p["genre"]) . "</td>";
        $html .= "<td>" . e((string) $p["category"]) . "</td>";
        $html .= "<td>" . number_format((float) $p["price"], 2, ".", " ") . " тг</td>";
        $html .= "<td>" . (int) $p["stock"] . "</td>";
        $html .= "</tr>";
    }
    
    $html .= "</table>";
    return $html;
}