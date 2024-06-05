<?php

namespace Drupal\phpunit_gen_tool\Commands;

/**
 * Trait OutputCommand.
 *
 * Provides methods to output messages with different foreground colors.
 */
trait OutputCommand {

  /**
   * Foreground color for success messages.
   *
   * @var string
   */
  protected $successForeground = 'green';

  /**
   * Foreground color for warning messages.
   *
   * @var string
   */
  protected $warningForeground = 'yellow';

  /**
   * Foreground color for error messages.
   *
   * @var string
   */
  protected $errorForeground = 'red';

  /**
   * The output interface.
   *
   * @var \Symfony\Component\Console\Output\OutputInterface|null
   */
  protected $output;

  /**
   * Write a success message.
   *
   * @param string $message
   *   The message to output.
   * @param bool $newLine
   *   Whether to add a new line after the message.
   *
   * @return static
   */
  protected function success(string $message, bool $newLine = TRUE): self {
    return $this->writeOutput($message, $this->successForeground, $newLine);
  }

  /**
   * Write a warning message.
   *
   * @param string $message
   *   The message to output.
   * @param bool $newLine
   *   Whether to add a new line after the message.
   *
   * @return static
   */
  protected function warning(string $message, bool $newLine = TRUE): self {
    return $this->writeOutput($message, $this->warningForeground, $newLine);
  }

  /**
   * Write an error message.
   *
   * @param string $message
   *   The message to output.
   * @param bool $newLine
   *   Whether to add a new line after the message.
   *
   * @return static
   */
  protected function error(string $message, bool $newLine = TRUE): self {
    return $this->writeOutput($message, $this->errorForeground, $newLine);
  }

  /**
   * Write a string to the output, optionally with a specified foreground color.
   *
   * @param string $string
   *   The string to output.
   * @param string|null $foreground
   *   The foreground color.
   * @param bool $newLine
   *   Whether to add a new line after the string.
   *
   * @return static
   */
  protected function writeOutput(string $string = '', ?string $foreground = NULL, bool $newLine = FALSE): self {
    if ($this->output->isQuiet()) {
      return $this;
    }

    if ($foreground !== NULL) {
      $string = "<fg={$foreground}>{$string}</>";
    }

    $this->output->writeln($string, $newLine);

    return $this;
  }

}
