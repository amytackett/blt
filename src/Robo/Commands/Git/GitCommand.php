<?php

namespace Acquia\Blt\Robo\Commands\Git;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "git:*" namespace.
 */
class GitCommand extends BltTasks {

  /**
   * Validates a git commit message.
   *
   * @command git:commit-msg
   *
   * @return int
   */
  public function commitMsgHook($message) {
    $this->say('Validating commit message syntax...');
    $prefix = $this->getConfigValue('project.prefix');
    if (!preg_match("/^$prefix-[0-9]+(: )[^ ].{15,}\\./", $message)) {
      $this->logger->error("Invalid commit message!");
      $this->say("Commit messages must:");
      $this->say("* Contain the project prefix followed by a hyphen");
      $this->say("* Contain a ticket number followed by a colon and a space");
      $this->say("* Be at least 15 characters long and end with a period.");
      $this->say("Valid example: $prefix-135: Added the new picture field to the article feature.");
      $this->say("");
      $this->logger->notice("To disable this command, see http://blt.rtfd.io/en/8.x/readme/extending-blt/#disabling-a-command");
      $this->logger->notice("To customize git hooks, see http://blt.rtfd.io/en/8.x/readme/extending-blt/#setupgit-hooks.");

      return 1;
    }

    return 0;
  }

  /**
   * Validates staged files.
   *
   * @command git:pre-commit
   *
   * @param string $changed_files
   *   A list of staged files, separated by \n.
   *
   * @return int
   */
  public function preCommitHook($changed_files) {
    $this->invokeCommands([
      'validate:phpcs:files' => ['file_list' => $changed_files],
      'validate:twig:files' => ['file_list' => $changed_files],
      'validate:yaml:files' => ['file_list' => $changed_files],
    ]);

    $changed_files_list = explode("\n", $changed_files);
    if (in_array(['composer.json', 'composer.lock'], $changed_files_list)) {
      $this->invokeCommand('validate:composer', ['file_list' => $changed_files]);
    }

    $this->invokeHook('pre-commit');
    $this->say("<info>Your local code has passed git pre-commit validation.</info>");
  }

}
