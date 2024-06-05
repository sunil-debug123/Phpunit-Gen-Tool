<?php

namespace Drupal\phpunit_gen_tool\Commands;

use Drupal\phpunit_gen_tool\Constants\Constants;
use Drupal\phpunit_gen_tool\Parsers\CodeParser;
use Drush\Commands\DrushCommands;

/**
 * Class for generate Test using Drush.
 */
class PhpUnitGenToolCommands extends DrushCommands {

  use OutputCommand;

  /**
   * Generate PHPUnit test for a given file.
   *
   * @param string $file_path
   *   The path to the file for which the test will be generated.
   *
   * @command phpunitgen:generate
   */
  public function generateTest($file_path) {
    $drupal_root = \Drupal::root();
    $cleaned_file_path = preg_replace('/^\.\//', '', $file_path);
    $cleaned_file_path = preg_replace('/^.*?\//', '', $cleaned_file_path);
    $original_file_path = $drupal_root . '/' . $cleaned_file_path;

    // Set initial counts to zero.
    $sourcesCount = 0;
    $successesCount = 0;
    $warningsCount = 0;
    $errorsCount = 0;
    $warnings = [];
    $errors = [];

    if (!file_exists($original_file_path)) {
      $errorsCount++;
      $errors[] = "No source to generate tests for: $file_path";
      $this->printResults($sourcesCount, $successesCount, $warningsCount, $errorsCount, $warnings, $errors);
      return;
    }

    // Increment sources count.
    $sourcesCount++;
    // Parse and validate the file.
    $parser = new CodeParser();
    $result = $parser->parseFile($original_file_path);
    if ($result[Constants::STATUS_KEY] == Constants::ERROR_STATUS) {
      $errorsCount++;
      $errors[] = $result[Constants::MESSAGE_KEY];
      $this->printResults($sourcesCount, $successesCount, $warningsCount, $errorsCount, $warnings, $errors);
      return;
    }
    // Extract the class name and namespace from the file.
    $class_info = $this->getClassInfo($original_file_path);

    if (!$class_info) {
      $errorsCount++;
      $errors[] = "Unable to extract class information from the file: $original_file_path";
      $this->printResults($sourcesCount, $successesCount, $warningsCount, $errorsCount, $warnings, $errors);
      return;
    }

    // Generate test code.
    $test_code = $this->generateTestCode($class_info['namespace'], $class_info['class_name'], $original_file_path);
    $test_file_path = $this->getTestFilePath($class_info['class_name']);

    if ($test_file_path && $test_code) {
      file_put_contents($test_file_path, $test_code);
    }
    else {
      $warningsCount++;
      $warnings[] = "Test file path or generated test code is invalid for class: {$class_info['class_name']}";
    }

    // If test generation is successful, increment successes count.
    if (file_exists($test_file_path)) {
      $successesCount++;
    }

    $executionTime = microtime(TRUE) - $_SERVER["REQUEST_TIME_FLOAT"];
    $this->printResults($sourcesCount, $successesCount, $warningsCount, $errorsCount, $warnings, $errors, $executionTime);
  }

  /**
   * Print the results of the test generation process.
   *
   * @param int $sourcesCount
   *   The number of sources identified.
   * @param int $successesCount
   *   The number of successful generations.
   * @param int $warningsCount
   *   The number of warnings.
   * @param int $errorsCount
   *   The number of errors.
   * @param array $warnings
   *   The array of warning messages.
   * @param array $errors
   *   The array of error messages.
   * @param float|null $executionTime
   *   The execution time.
   */
  protected function printResults($sourcesCount, $successesCount, $warningsCount, $errorsCount, $warnings, $errors, $executionTime = NULL) {
    $this->writeOutput();
    $this->writeOutput('Generation is finished!', NULL, TRUE);
    $this->writeOutput();
    $this->writeOutput($sourcesCount . ' source(s) identified', NULL, TRUE);
    $this->success($successesCount . ' success(es)');
    $this->warning($warningsCount . ' warning(s)');
    $this->error($errorsCount . ' error(s)');

    // Print all warnings.
    if (!empty($warnings)) {
      $this->warning("Warnings:");
      foreach ($warnings as $warning) {
        $this->writeOutput("- $warning");
      }
    }

    // Print all errors.
    if (!empty($errors)) {
      $this->writeOutput();
      $this->error("Errors:");
      foreach ($errors as $error) {
        $this->writeOutput("- $error");
      }
    }

    $this->writeOutput();
    if ($executionTime !== NULL) {
      $this->writeOutput("Execution time: " . number_format($executionTime, 3) . " s");
    }
  }

  /**
   * Extract the class name and namespace from the file.
   *
   * @param string $file_path
   *   The path to the file.
   *
   * @return array|false
   *   An associative array with 'namespace' and 'class_name' keys, or FALSE on failure.
   */
  protected function getClassInfo($file_path) {
    $contents = file_get_contents($file_path);
    $namespace = '';
    // Get the class name.
    $class_name = basename($file_path, '.php');

    if (preg_match('/namespace\s+([^;]+);/', $contents, $matches)) {
      $namespace = $matches[1];
    }

    if ($namespace && $class_name) {
      return ['namespace' => $namespace, 'class_name' => $class_name];
    }

    return FALSE;
  }

  /**
   * Generate the PHPUnit test code.
   *
   * @param string $namespace
   *   The namespace of the class to be tested.
   * @param string $class_name
   *   The name of the class to be tested.
   * @param string $file_path
   *   The path to the file.
   *
   * @return string
   *   The generated test code.
   */
  protected function generateTestCode($namespace, $class_name, $file_path) {
    $test_namespace = $namespace . '\\Tests';
    $test_class_name = $class_name . 'Test';
    $test_methods = $this->generateTestMethods($file_path);

    return <<<EOT
<?php

namespace $test_namespace;

use PHPUnit\Framework\TestCase;

class $test_class_name extends TestCase {

  public function setUp(): void {
    parent::setUp();
    // Setup code here.
  }

$test_methods

}

EOT;
  }

  /**
   * Generate PHPUnit test methods for all functions/methods in the file.
   *
   * @param string $file_path
   *   The path to the file.
   *
   * @return string
   *   The generated test methods.
   */
  protected function generateTestMethods($file_path) {
    $contents = file_get_contents($file_path);
    preg_match_all('/(?:public|protected|private|static)?\s*function\s*(\w+)\s*\(/', $contents, $matches);

    $test_methods = '';
    foreach ($matches[1] as $method_name) {
      if ($method_name !== '__construct' && strpos($method_name, '__') !== 0) {
        $test_method_name = lcfirst($method_name);
        $test_methods .= $this->generateTestMethod($test_method_name);
      }
    }

    return $test_methods;
  }

  /**
   * Generate a PHPUnit test method for a function/method.
   *
   * @param string $method_name
   *   The name of the function/method.
   *
   * @return string
   *   The generated test method.
   */
  protected function generateTestMethod($method_name) {
    $testMethodName = ucfirst($method_name);
    return <<<EOT

  public function test$testMethodName() {
    // Implement test for $method_name here.
    \$this->assertTrue(true);
  }
EOT;
  }

  /**
   * Determine the test file path based on the original file path.
   *
   * @param string $class_name
   *   The name of the class to be tested.
   *
   * @return string
   *   The test file path.
   */
  protected function getTestFilePath($class_name) {
    $module_path = \Drupal::service('extension.list.module')->getPath('phpunit_gen_tool');

    $test_directory = $module_path . '/tests/src/Unit';
    if (!is_dir($test_directory)) {
      mkdir($test_directory, 0777, TRUE);
    }

    return $test_directory . '/' . $class_name . 'Test.php';
  }

}
