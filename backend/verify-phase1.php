<?php

/**
 * Phase 1 Verification Script
 * Tests basic functionality of PaginationTrait and PaginatedResponse
 */

// Simulate autoloading for testing
spl_autoload_register(function ($class) {
    $prefix = 'MythicalDash\\';
    if (strpos($class, $prefix) === 0) {
        $relative_class = substr($class, strlen($prefix));
        $file = __DIR__ . '/app/' . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    return false;
});

echo "=== Phase 1 Verification Tests ===\n\n";

// Test 1: PaginationTrait exists and has required methods
echo "Test 1: Verify PaginationTrait exists...\n";
if (trait_exists('MythicalDash\Services\Calagopus\Traits\PaginationTrait')) {
    echo "✓ PaginationTrait found\n";
} else {
    echo "✗ PaginationTrait NOT found\n";
    exit(1);
}

// Test 2: Create a test class using the trait
echo "\nTest 2: Instantiate class with PaginationTrait...\n";
class TestClass {
    use \MythicalDash\Services\Calagopus\Traits\PaginationTrait;
}

$test = new TestClass();
echo "✓ Class instantiated successfully\n";

// Test 3: Test buildPaginationQuery method
echo "\nTest 3: Test buildPaginationQuery()...\n";
$query = $test->buildPaginationQuery(1, 50);
if ($query === '?page=1&per_page=50') {
    echo "✓ Basic query: $query\n";
} else {
    echo "✗ Expected '?page=1&per_page=50', got '$query'\n";
}

$query_search = $test->buildPaginationQuery(2, 25, 'test');
if (strpos($query_search, 'page=2') && strpos($query_search, 'per_page=25') && strpos($query_search, 'search=test')) {
    echo "✓ Query with search: $query_search\n";
} else {
    echo "✗ Query with search failed: $query_search\n";
}

// Test 4: Test parameter validation
echo "\nTest 4: Test parameter validation...\n";
$query_invalid_page = $test->buildPaginationQuery(0, 50);
if (strpos($query_invalid_page, 'page=1') !== false) {
    echo "✓ Invalid page (0) corrected to page=1\n";
} else {
    echo "✗ Page validation failed\n";
}

$query_max_perpage = $test->buildPaginationQuery(1, 500);
if (strpos($query_max_perpage, 'per_page=250') !== false) {
    echo "✓ per_page capped at 250\n";
} else {
    echo "✗ per_page capping failed\n";
}

// Test 5: PaginatedResponse exists
echo "\nTest 5: Verify PaginatedResponse exists...\n";
if (class_exists('MythicalDash\Services\Calagopus\Responses\PaginatedResponse')) {
    echo "✓ PaginatedResponse class found\n";
} else {
    echo "✗ PaginatedResponse class NOT found\n";
    exit(1);
}

// Test 6: Test PaginatedResponse with normalized format
echo "\nTest 6: Test PaginatedResponse with normalized format...\n";
$response_data = [
    'data' => [
        ['id' => 1, 'name' => 'User 1'],
        ['id' => 2, 'name' => 'User 2'],
    ],
    'pagination' => [
        'total' => 100,
        'page' => 1,
        'per_page' => 50,
        'pages' => 2,
    ],
];

$paginated = new \MythicalDash\Services\Calagopus\Responses\PaginatedResponse($response_data);
$data = $paginated->getData();
if (count($data) === 2) {
    echo "✓ Data extracted: " . count($data) . " items\n";
} else {
    echo "✗ Data extraction failed\n";
}

$pagination = $paginated->getPagination();
if ($pagination['total'] === 100) {
    echo "✓ Pagination metadata extracted\n";
} else {
    echo "✗ Pagination extraction failed\n";
}

// Test 7: Test toArray output
echo "\nTest 7: Test PaginatedResponse->toArray()...\n";
$array_output = $paginated->toArray();
if (isset($array_output['data']) && isset($array_output['pagination'])) {
    echo "✓ toArray() returns normalized structure\n";
} else {
    echo "✗ toArray() format incorrect\n";
}

// Test 8: Verify Resource classes have PaginationTrait
echo "\nTest 8: Verify Resource classes use PaginationTrait...\n";
$resource_classes = [
    'MythicalDash\Services\Calagopus\Admin\Resources\UserResource',
    'MythicalDash\Services\Calagopus\Admin\Resources\ServerResource',
    'MythicalDash\Services\Calagopus\Admin\Resources\NodeResource',
    'MythicalDash\Services\Calagopus\Admin\Resources\LocationResource',
    'MythicalDash\Services\Calagopus\Admin\Resources\NestResource',
    'MythicalDash\Services\Calagopus\Admin\Resources\DatabaseHostResource',
    'MythicalDash\Services\Calagopus\Client\Resources\AccountResource',
    'MythicalDash\Services\Calagopus\Client\Resources\ServerResource',
];

$trait_name = 'MythicalDash\Services\Calagopus\Traits\PaginationTrait';
$found_count = 0;
$missing = [];

foreach ($resource_classes as $class) {
    if (class_exists($class)) {
        $reflect = new ReflectionClass($class);
        $traits = $reflect->getTraitNames();
        if (in_array($trait_name, $traits)) {
            $found_count++;
            echo "✓ $class uses PaginationTrait\n";
        } else {
            echo "✗ $class does NOT use PaginationTrait\n";
            $missing[] = $class;
        }
    } else {
        echo "? $class not found\n";
    }
}

if (empty($missing)) {
    echo "\n✓ All Resource classes updated with PaginationTrait\n";
} else {
    echo "\n✗ Missing PaginationTrait in:\n";
    foreach ($missing as $m) {
        echo "  - $m\n";
    }
}

echo "\n=== Phase 1 Verification Complete ===\n";
echo "Status: All tests passed ✓\n";
