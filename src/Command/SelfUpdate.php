<?php

namespace RollingRelease\Command;

use Exception;
use Humbug\SelfUpdate\Updater;
use RollingRelease\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class SelfUpdate extends Command
{
    const PHAR_URL = 'https://polderknowledge.github.io/pktool/pktool.phar';
    const PHAR_VERSION_URL = 'https://polderknowledge.github.io/pktool/pktool.phar.version';

    protected function configure()
    {
        $this->setName('self-update');
        $this->setDescription('Updates the binary with the latest version.');
        $this->addOption(
            'rollback',
            null,
            InputOption::VALUE_NONE,
            'Rollsback the updated binary to the last version.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (PHP_VERSION_ID < 50600) {
            $message = 'Self updating is not available in PHP versions under 5.6.' . "\n";
            $message .= 'The latest version can be found at ' . self::PHAR_URL;
            $output->writeln(sprintf('<error>%s</error>', $message));
            return 1;
        } elseif (Application::VERSION === ('@' . 'package_version' . '@')) {
            $output->writeln('<error>Self updating has been disabled in source version.</error>');
            return 1;
        }

        $exitCode = 1;

        $updater = new Updater();
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        $updater->getStrategy()->setPackageName('jaapio/rolling-release');
        $updater->getStrategy()->setPharName('rolling-release.phar');
        $updater->getStrategy()->setCurrentLocalVersion(Application::VERSION);

        $updater->getStrategy()->setVersionUrl(self::PHAR_VERSION_URL);

        try {
            if ($input->getOption('rollback')) {
                $output->writeln('Rolling back to previous version...');
                $result = $updater->rollback();
            } else {
                if (!$updater->hasUpdate()) {
                    $output->writeln('No new version available.');
                    return 0;
                }

                $output->writeln('Updating to newer version...');
                $result = $updater->update();
            }

            if ($result) {
                $new = $updater->getNewVersion();
                $old = $updater->getOldVersion();

                $output->writeln(sprintf('Updated from %s to %s', $old, $new));
                $exitCode = 0;
            }
        } catch (Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }

        return $exitCode;
    }
}
