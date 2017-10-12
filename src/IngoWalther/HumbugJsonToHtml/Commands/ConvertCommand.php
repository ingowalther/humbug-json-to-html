<?php

namespace IngoWalther\HumbugJsonToHtml\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertCommand extends Command
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @param \Twig_Environment $twig
     */
    public function setTwig($twig)
    {
        $this->twig = $twig;
    }

    protected function configure()
    {
        $this
            ->setName('convert')
            ->setDescription('Converts humbug Json-Reports to Html-Reports')
            ->addOption('input', 'i', InputOption::VALUE_REQUIRED, 'Path to Json-Report')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Path to Output-Html', getcwd() . '/humbug.html');
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jsonReportPath = $input->getOption('input');
        if(!$jsonReportPath) {
            throw new \InvalidArgumentException('You need to set the path to the Json-Report');
        }

        $jsonReportPath = $this->buildFullPath($jsonReportPath);

        if(!is_readable($jsonReportPath)) {
            throw new \InvalidArgumentException(sprintf('Json-Report "%s" is not readable', $jsonReportPath));
        }

        $report = json_decode(file_get_contents($jsonReportPath));

        if(!$report) {
            throw new \InvalidArgumentException(sprintf('Json-Report "%s" is no valid Json', $jsonReportPath));
        }

        $outputPath = $input->getOption('output');
        $outputPath = $this->buildFullPath($outputPath);

        if(file_exists($outputPath) && !is_writable($outputPath)) {
            throw new \InvalidArgumentException(sprintf('File "%s" is not writable', $outputPath));
        }

        if(!file_exists($outputPath) && !is_writable(dirname($outputPath))) {
            throw new \InvalidArgumentException(sprintf('Directory "%s" is not writable', dirname($outputPath)));
        }

        $template = $this->twig->render('index.html', ['report' => $report]);
        file_put_contents($outputPath, $template);

        $output->writeln('<info>Report successfully converted</info>');
        $output->writeln('Saved to: ' . $outputPath);
    }

    /**
     * @param $filePath
     * @return string
     */
    private function buildFullPath($filePath)
    {
        if ($filePath && substr(pathinfo($filePath)['dirname'], 0, 1) != '/') {
            $filePath = getcwd() . '/' . $filePath;
        }
        return $filePath;
    }
}