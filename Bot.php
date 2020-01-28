<?php

namespace EG\Components;

use EG\Components\YaSdoh;
use EG\Components\ConsoleLog;
use Closure;

/**
 * Класс для создания функционала роботов
 * Class Bot
 * @package EG\Components
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
    private $sdoh;

    /**
     * Путь для блокирующего файла
     * @var string
     */
    private $lockFile = '';

    /**
     * Время жизни блокирующего файла
     * @var int
     */
    private $lockFileTime = 3600;

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
    private function initialize()
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
    private function setSequentialOutput()
    {
        ini_set('output_buffering', 'off');

        ini_set('implicit_flush', true);
    }

    /**
     * Вызывает коллбек в случае передачи скрипту параметра $option
     * Можно указать обязательное значение параметра для вызова коллбека
     * @param string $option Параметр параметра командной строки
     * @param Closure $callback Коллбек
     * @param null $needleValue Если указать, тогда коллбек вызовется только в случае совпадения его со значением параметра
     */
    protected function setScriptOptionCallback($option, Closure $callback, $needleValue = null)
    {
        $params = [
            "{$option}::" => "{$option}::",
        ];

        $options = getopt(implode('', array_keys($params)), $params);

        if (!isset($options[$option]))
            return;

        if (isset($needleValue) && $options[$option] != $needleValue)
            return;

        $callback->__invoke($options[$option]);
    }

    /**
     * Позволяет некоторые параметры вывода в консоль поменять из командной строки
     */
    private function setLogOptions()
    {
        $this->setScriptOptionCallback('output', function ($outputValue) {
            $this->log->setOutputToConsole((bool)$outputValue);
        });

        $this->setScriptOptionCallback('only-errors', function ($outputErrorsValue) {
            $this->log->setOutputOnlyIfError((bool)$outputErrorsValue);
        });
    }

    /**
     * Начинаем работу с роботом, передавая ему коллбек в случае некорректного завершения робота
     * @param Closure $callback
     */
    protected function start(Closure $callback)
    {
        $this->sdoh->setCallback($callback);
    }

    /**
     * Настраивает коллбек для добавления записи в лог
     * @param Closure $callback
     */
    protected function log(Closure $callback)
    {
        $this->log->setLogCallback($callback);
    }

    /**
     * Завершаем работу с роботом
     */
    protected function finish()
    {
        $this->sdoh->setStatusDone();
    }

    /**
     * Настройки блокировочного файла
     * @param string $filePath Путь к блокировочному файлу
     * @param null|int $lockTime Время жизни блокировочного файла
     */
    protected function setupLock($filePath, $lockTime = null)
    {
        $this->lockFile = $filePath;

        if ($lockTime)
            $this->lockFileTime = $lockTime;
    }

    /**
     * Возвращает тру, если всё огонь и можно продолжать работу
     * @return bool
     */
    protected function canWeWork()
    {
        $this->log->text('Проверяем, имеем ли мы право работать в этом инстансе бота');

        if (!$this->isUnlock()) {
            return false;
        }

        if (!$this->setLock()) {
            return false;
        }

        return true;
    }

    /**
     * Ставит блокировку
     * @return bool
     */
    protected function setLock()
    {
        $this->log->text('Создаём блокировочный файл...');

        if (!file_put_contents($this->lockFile, time())) {
            $this->log->error('Не удалось записать блокировочный файл!');

            return false;
        }

        return true;
    }

    /**
     * Возвращает тру, если нет файла блокировки
     * @return bool
     */
    protected function isUnlock()
    {
        if (file_exists($this->lockFile)) {
            $this->log->warning('Есть блокировочный файл');

            $time = file_get_contents($this->lockFile);

            if ((time() - $time) > $this->lockFileTime) {
                $this->log->warning('Блокировочный файл лежит слишком долго и устарел!');

                return true;
            }

            $this->log->warning('Блокировный файл не устарел, значит просто выходим...');

            return false;
        }

        return true;
    }

    /**
     * Удаление блокировочного файла
     */
    protected function deleteLockFile()
    {
        if (!$this->lockFile)
            return;

        $this->log->text('Удаление блокировочного файла');

        unlink($this->lockFile);
    }
}
