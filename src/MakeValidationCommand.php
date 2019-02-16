<?php

namespace Jijoel\ValidationRuleGenerator;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use InvalidArgumentException;


class MakeValidationCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:validation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate validation rules for a given model';


    protected $generator;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($generator)
    {
        $this->generator = $generator;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        if ($this->option('all')) {
            var_export($this->generator->getRules());
            return;
        }

        $table = $this->option('table');
        if ($table) {
            var_export($this->generator->getRules($table));
            return;
        }

        $model = $this->option('model');
        if ($model) {
            $instance = new $model;
            var_export($this->generator->getRules(
                $instance->getTable(),
                Null,
                $model::$rules));
            return;
        }

        throw new InvalidArgumentException('What would you like to generate rules for?');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            // array('model', InputArgument::OPTIONAL, 'The model for which you would like to generate rules.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('model', null, InputOption::VALUE_REQUIRED, 'Model for which to generate rules (include overrides)'),
            array('table', null, InputOption::VALUE_REQUIRED, 'Table for which to generate rules'),
            array('all', null, InputOption::VALUE_NONE, 'Generate rules for all tables'),
        );
    }


}

