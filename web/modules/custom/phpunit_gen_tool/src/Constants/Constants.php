<?php

namespace Drupal\phpunit_gen_tool\Constants;

/**
 * Class Constants for defining error and success messages.
 */
class Constants {
  public const ERROR_STATUS = "error";
  public const SUCCESS_STATUS = "success";
  public const STATUS_KEY = "status";
  public const MESSAGE_KEY = "message";

  public const FILE_NOT_FOUND_MESSAGE = "File does not exist: %s";
  public const FILE_EMPTY_MESSAGE = "The file is empty: %s";
  public const SYNTAX_ERROR_MESSAGE = "Syntax errors found in the file: %s";
  public const FILE_CORRECT_MESSAGE = "The file is correct.";
  public const NO_TESTABLE_CODE_MESSAGE = "The file does not contain any valid PHP classes or functions: %s";
  public const NO_SYNTAX_ERRORS = "No syntax errors";
  public const MODULE_DIR = "/modules";

}
