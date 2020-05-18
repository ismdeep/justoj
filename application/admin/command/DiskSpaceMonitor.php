<?php

namespace app\admin\command;


use think\console\Command;
use think\console\Input;
use think\console\Output;

class DiskSpaceMonitor extends Command {

    protected function configure() {
        $this->setName('diskspacemonitor')->setDescription('Disk Space Monitor');
    }

    protected function execute(Input $input, Output $output) {
        $output->writeln('Disk Space Monitor');
    }

}