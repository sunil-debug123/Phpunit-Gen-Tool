<?php

namespace Drupal\phpunit_gen_tool\Parsers;

use Drupal\phpunit_gen_tool\Constants\Constants;

/**
 * Class CodeParser for parsing PHP files and checking their syntax.
 */
class CodeParser {

  /**
   * Parse and check the syntax of a PHP file.
   *
   * @param string $file_path
   *   The path to the PHP file.
   *
   * @return array
   *   An associative array containing 'status' and 'message'.
   */
  public function parseFile($file_path) {
    $file_path_dir = substr($file_path, strpos($file_path, Constants::MODULE_DIR));
    $result = [
      Constants::STATUS_KEY => Constants::SUCCESS_STATUS,
      Constants::MESSAGE_KEY => Constants::FILE_CORRECT_MESSAGE,
    ];

    // Check if the file exists.
    if (!file_exists($file_path)) {
      $result[Constants::STATUS_KEY] = Constants::ERROR_STATUS;
      $result[Constants::MESSAGE_KEY] = sprintf(Constants::FILE_NOT_FOUND_MESSAGE, $file_path_dir);
      return $result;
    }

    // Get the contents of the file.
    $contents = file_get_contents($file_path);

    // Check if the file is empty.
    if (empty($contents)) {
      $result[Constants::STATUS_KEY] = Constants::ERROR_STATUS;
      $result[Constants::MESSAGE_KEY] = sprintf(Constants::FILE_EMPTY_MESSAGE, $file_path_dir);
      return $result;
    }

    // Check for basic PHP syntax errors.
    if (!$this->isSyntaxCorrect($contents)) {
      $result[Constants::STATUS_KEY] = Constants::ERROR_STATUS;
      $result[Constants::MESSAGE_KEY] = sprintf(Constants::SYNTAX_ERROR_MESSAGE, $file_path_dir);
      return $result;
    }

    // Check if the file contains classes or functions that can be tested.
    if (!$this->containsTestableCode($contents)) {
      $result[Constants::STATUS_KEY] = Constants::ERROR_STATUS;
      $result[Constants::MESSAGE_KEY] = sprintf(Constants::NO_TESTABLE_CODE_MESSAGE, $file_path_dir);
      return $result;
    }

    return $result;
  }

  /**
   * Check if the PHP code syntax is correct.
   *
   * @param string $code
   *   The PHP code as a string.
   *
   * @return bool
   *   TRUE if the syntax is correct, FALSE otherwise.
   */
  protected function isSyntaxCorrect($code) {
    // Suppress output and run the PHP code through eval().
    // Wrap the code in a function to avoid execution of any code.
    $code = '<?php function testSyntax() { ' . $code . ' } ?>';

    // Write the code to a temporary file.
    $temp_file = tempnam(sys_get_temp_dir(), 'php');
    file_put_contents($temp_file, $code);

    // Check the syntax of the file.
    $output = shell_exec("php -l $temp_file 2>&1");
    unlink($temp_file);

    // Check if the output contains "No syntax errors".
    return strpos($output, Constants::NO_SYNTAX_ERRORS) !== FALSE;
  }

  /**
   * Check if the PHP code contains valid PHP classes or functions.
   *
   * @param string $code
   *   The PHP code as a string.
   *
   * @return bool
   *   TRUE if the code contains valid PHP classes or functions.
   */
  protected function containsTestableCode($code) {
    // Check if the code contains valid PHP class or function definitions.
    return preg_match('/<\?php.*?\b(?:class|function)\s+\w+\s*\(/s', $code) === 1;
  }

}
