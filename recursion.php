<?php
declare(strict_types=1);

$GLOBALS["categories"] = [
  [
    "id" => 1,
    "name" => "A",
    "parent_categories" => null,
  ],
  [
    "id" => 2,
    "name" => "B",
    "parent_categories" => [1],
  ],
  [
    "id" => 3,
    "name" => "C",
    "parent_categories" => [2],
  ],
  [
    "id" => 4,
    "name" => "D",
    "parent_categories" => [3],
  ],
  [
    "id" => 5,
    "name" => "E",
    "parent_categories" => [2],
  ],
];

$GLOBALS["products"] = [
  [
    "id" => 1,
    "name" => "1",
    "categories" => [1,2],
  ],
  [
    "id" => 2,
    "name" => "2",
    "categories" => [2,3],
  ],
  [
    "id" => 3,
    "name" => "3",
    "categories" => [1],
  ],
  [
    "id" => 4,
    "name" => "4",
    "categories" => [4],
  ],
  [
    "id" => 5,
    "name" => "5",
    "categories" => [4,5],
  ],
];

function findProductById(int $productId): array
{
  $products = $GLOBALS["products"];
  $results = array_filter(
    $products,
    function($product) use ($productId) {
      return $product["id"] === $productId;
    }
  );

  return reset($results);
}

function findCategoryById(int $categoryId): array
{
  $categories = $GLOBALS["categories"];
  $results = array_filter(
    $categories,
    function($category) use ($categoryId) {
      return $category["id"] === $categoryId;
    }
  );

  return reset($results);
}

function getParentCategories(int $categoryId): array
{
  $category = findCategoryById($categoryId);
  $parentCategories = $category["parent_categories"];

  if(is_null($parentCategories) || (is_array($parentCategories) && count($parentCategories) === 0))
    return [];

  $categories = [];

  foreach($parentCategories as $parentCategoryId) {
    $categories[] = $parentCategoryId;
    $categories = array_merge($categories, getParentCategories($parentCategoryId));
  }

  return $categories;
}

function getAllCategoriesProductBelongsTo(int $productId): array
{
  $product = findProductById($productId);
  $productCategories = $product["categories"];

  if(is_null($productCategories) || (is_array($productCategories) && count($productCategories) === 0))
    return [];

  $categories = [];

  foreach($productCategories as $categoryId) {
    $categories[] = $categoryId;
    $categories = array_merge($categories, getParentCategories($categoryId));
  }

  $categories = array_unique($categories);
  sort($categories);

  return $categories;
}

function getParentCategoryPaths(array $currentPath, int $categoryId): array
{
  $category = findCategoryById($categoryId);
  $parentCategories = $category["parent_categories"];

  if(is_null($parentCategories) || (is_array($parentCategories) && count($parentCategories) === 0))
    return [$currentPath];

    $paths = [];

  foreach($parentCategories as $parentCategoryId) {
    $paths = array_merge(
      $paths,
      getParentCategoryPaths(
        array_merge([$parentCategoryId], $currentPath),
        $parentCategoryId
      )
    );
  }

  return $paths;
}

function getAllCategoryPathsForProduct(int $productId): array
{
  $product = findProductById($productId);
  $productCategories = $product["categories"];

  if(is_null($productCategories) || (is_array($productCategories) && count($productCategories) === 0))
    return [];

  $paths = [];

  foreach($productCategories as $categoryId) {
    $paths = array_merge(getParentCategoryPaths([$categoryId], $categoryId), $paths);
  }

  return $paths;
}

$paths = getAllCategoryPathsForProduct(2);
$product = findProductById(2);
$productName = $product["name"];

foreach($paths as $path) {
  $categoryNames = array_map(
    function($categoryId) {
      $category = findCategoryById($categoryId);
      return $category["name"];
    },
    $path
  );


  printf(
    "%s<-%s%s",
    implode("<-", $categoryNames),
    $productName,
    PHP_EOL
  );
}
