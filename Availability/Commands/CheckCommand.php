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

class CheckCommand extends Command
{
	protected function configure() {
		$this->setName("check")
			->setDescription("Checks if server available")
			->setDefinition(array(
				new InputArgument('destination',InputArgument::REQUIRED,'Site destination to check'),
				new InputOption('log','l',InputOption::VALUE_OPTIONAL,'Save output to log file',true),
				new InputOption('logName','N',InputOption::VALUE_OPTIONAL,'Prefix name of the log file','available.log'),
				new InputOption('logDir','d',InputOption::VALUE_OPTIONAL,'Where log files should be stored','/tmp'),
				new InputOption('logFileSize','s',InputOption::VALUE_OPTIONAL,'File length in kb untill rotate',1024),
				new InputOption('logMaxFiles','m',InputOption::VALUE_OPTIONAL,'Number of files used for rotation',8),
				new InputOption('rotate','r',InputOption::VALUE_OPTIONAL,'Rotate or overwrite log files',true),
			))
			->setHelp(<<<EOT
The <info>check</info> command outputs <info>destinations</info> headers or saves them to file
EOT
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$header = $this->getStatusHeader('http://'.$input->getArgument('destination'));
		$output->writeln($header.' for http://'.$input->getArgument('destination'));

		if ($input->getOption('log')){
			$this->saveLog($header,$input->getArgument('destination'),$input->getOption('logDir'),$input->getOption('logName'),$input->getOption('logFileSize'),$input->getOption('logMaxFiles'));
		}

	}

	/**
	 * Get response headers.
	 * @param $url
	 * @return bool|string
	 */
	protected function getStatusHeader($url)
	{
		set_error_handler(function($errno, $errstr, $errfile, $errline){
			throw new \Exception('Could not resolve connection: '.$errstr);
		},E_ALL);

		try{
			$headers = get_headers($url);
			$result = $headers[0];
		}catch (\Exception $e){
			$result = 'No connection';
		}

		restore_error_handler();

		return $result;
	}


	/**
	 * Saves log into file
	 * @param $response string response to save
	 * @param $destination string
	 * @param $filePath string directory of logs
	 * @param $fileName string file name
	 * @param $fileSize int size of file in kb
	 * @param $maxFiles int number of files used for rotation
	 */
	protected function saveLog($response,$destination,$filePath,$fileName,$fileSize,$maxFiles)
	{
		$logFile=$filePath.DIRECTORY_SEPARATOR.$fileName;
		if(is_readable($logFile) && filesize($logFile)>$fileSize*1024)
			$this->rotateFiles($filePath,$fileName,$maxFiles);
		$fp=fopen($logFile,'a');
		flock($fp,LOCK_EX);
		fwrite($fp,date('Y-m-d H:i:s')."\t".$destination."\t".$response.PHP_EOL);
		flock($fp,LOCK_UN);
		fclose($fp);
	}

	/**
	 * Rotates log files.
	 * @param $filePath string directory of logs
	 * @param $fileName string file name
	 * @param $maxFiles int number of files used for rotation
	 */
	protected function rotateFiles($filePath,$fileName,$maxFiles)
	{
		$file=$filePath.DIRECTORY_SEPARATOR.$fileName;

		for($i=$maxFiles;$i>0;--$i)
		{
			$rotateFile=$file.'.'.$i;
			if(is_file($rotateFile))
			{
				// suppress errors because it's possible multiple processes enter into this section
				if($i===$maxFiles)
					unlink($rotateFile);
				else
					rename($rotateFile,$file.'.'.($i+1));
			}
		}

		if(is_file($file))
			rename($file,$file.'.1'); // suppress errors because it's possible multiple processes enter into this section
	}
}