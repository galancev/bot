<?php

namespace Galantcev\Components;

use Galantcev\Components\YaSdoh;
use Galantcev\Components\ConsoleLog;

/**
 * Класс для создания функционала роботов
 * Class Bot
 * @package Components
 */
class Bot
{
    /**
     * Через него идёт вывод лога работы на экран или в файл
     * @var ConsoleLog
     */
    public $log;

    /**
     * Хендлер некорректного завершения
     * @var YaSdoh
     */
    public $sdoh;

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Инициализирование робота
     */
    protected function initialize()
    {
        set_time_limit(0);

        $this->setSequentialOutput();

        $this->log = new ConsoleLog();
        $this->setLogOptions();

        $this->sdoh = new YaSdoh();
    }

    /**
     * Убирает буфферизацию вывода
     */
    protected function setSequentialOutput()
    {
        ini_set('output_buffering', 'off');

        ini_set('implicit_flush', true);
    }

    /**
     * Позволяет некоторые параметры вывода в консоль поменять из командной строки
     */
    protected function setLogOptions()
    {
        global $argv;

        foreach ($argv as $arg) {
            switch ($arg) {
                case '--output=0':
                    $this->log->setOutputToConsole(false);

                    break;

                case '--output=1':
                    $this->log->setOutputToConsole(true);

                    break;

                case '--only-errors=0':
                    $this->log->setOutputOnlyIfError(false);

                    break;

                case '--only-errors=1':
                    $this->log->setOutputOnlyIfError(true);

                    break;

            }
        }
    }

    /**
     * Начинаем работу с роботом, передавая ему коллбек в случае некорректного завершения робота
     * @param \Closure $callback
     */
    protected function start($callback)
    {
        $this->sdoh->setCallback($callback);
    }

    /**
     * Завершаем работу с роботом
     */
    protected function finish()
    {
        $this->sdoh->setStatusDone();
    }
}
