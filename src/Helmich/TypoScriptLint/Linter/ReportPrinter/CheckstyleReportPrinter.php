<?php
namespace Helmich\TypoScriptLint\Linter\ReportPrinter;

use Helmich\TypoScriptLint\Linter\Report\Report;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Report printer that generates XML documents as generated by checkstyle [1].
 *
 * These reports are well-suited for being used in continuous integration
 * environments like Jenkins [2].
 *
 * [1] http://checkstyle.sourceforge.net/
 * [2] http://jenkins-ci.org/
 *
 * @package    Helmich\TypoScriptLint
 * @subpackage Linter\ReportPrinter
 */
class CheckstyleReportPrinter implements Printer
{

    /** @var OutputInterface */
    private $output;

    /**
     * Constructs a new checkstyle report printer.
     *
     * @param OutputInterface $output Output stream to write on. Might be STDOUT or a file.
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Writes a report in checkstyle XML format.
     *
     * @param Report $report The report to print.
     * @return void
     */
    public function writeReport(Report $report)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');

        $root = $xml->createElement('checkstyle');
        $root->setAttribute('version', APP_NAME . '-' . APP_VERSION);

        foreach ($report->getFiles() as $file) {
            $xmlFile = $xml->createElement('file');
            $xmlFile->setAttribute('name', $file->getFilename());

            foreach ($file->getWarnings() as $warning) {
                $xmlWarning = $xml->createElement('error');
                $xmlWarning->setAttribute('line', $warning->getLine());
                $xmlWarning->setAttribute('severity', $warning->getSeverity());
                $xmlWarning->setAttribute('message', $warning->getMessage());
                $xmlWarning->setAttribute('source', $warning->getSource());

                if ($warning->getColumn() !== null) {
                    $xmlWarning->setAttribute('column', $warning->getColumn());
                }

                $xmlFile->appendChild($xmlWarning);
            }

            $root->appendChild($xmlFile);
        }

        $xml->appendChild($root);
        $xml->formatOutput = true;

        $this->output->write($xml->saveXML());
    }
}
