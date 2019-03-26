<?php
/**
 * Пример создания роботов
 */

namespace {
    // Глобальные инициализации

    include 'vendor/autoload.php';
}

namespace bot {

    use Galantcev\Components\Bot;

    /**
     * Пример робота
     * Class SampleBot
     * @package bot
     */
    class SampleBot extends Bot
    {
        /**
         * @inheritdoc
         */
        public function __construct()
        {
            parent::__construct();

            // Будем блокировать повторные запуски робота
            $this->setupLock(__FILE__ . '.lock', 60 * 60 * 1);


            // Устанавливаем коллбек в случае некорректного завершения робота
            $this->start(function () {
                $this->log->error('А вот тут у нас случилось прерывание, а ничего успешно не завершено!');

                $this->deleteLockFile();
            });

            // Выводим справку в случае переданного параметра --help
            $this->setScriptOptionCallback('help', function () {
                $this->log->setUseTimestamps(false);
                $this->log->setShowLabels(false);

                $this->log->text('Этот скрипт сравнивает горы своей мощной функциональностью');

                $this->finish();

                exit;
            });
        }

        /**
         * Робот что-нибудь делает
         */
        public function go()
        {
            // Можем ли мы вообще работать или должны сушить ласты
            if (!$this->canWeWork()) {
                $this->finish();

                return;
            }

            $this->log->text('Текст');
            $this->log->error('Ошибка!');
            $this->log->warning('Внимание.');
            $this->log->success('Успех :)');

            // А здесь говорим, что на самом деле всё хорошо отработало
            $this->finish();
            $this->deleteLockFile();
        }
    }

    $bot = new SampleBot();

    $bot->log->text('Начинаем работу!');

    $bot->go();

    $bot->log->text('Завершаем работу.');
}
