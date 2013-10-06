<?php
/**
 * @author Ivan Matveev <Redjiks@gmail.com>.
 */

namespace Availability\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ParseCommand extends Command
{
	protected function configure() {
		$this->setName("parse")
			->setDescription("Parses log files into db")
			->setDefinition(array(
			))
			->setHelp(<<<EOT
The <info>test</info> command does things and stuff
EOT
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		//...
	}
}