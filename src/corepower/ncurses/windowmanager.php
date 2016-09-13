<?php
namespace CorePower\Ncurses
{
    use CorePower\Ncurses\Containers\KeyEvent;
    use CorePower\Ncurses\Containers\MouseClickEvent;
    use CorePower\Ncurses\Shared\Colors;
    use CorePower\Ncurses\Widgets\Window;

    class WindowManager
    {
        protected static $running = false;

        public static function isRunning() : bool
        {
            return self::$running;
        }

        public static function run(Window $window)
        {
            self::$running = true;
            Colors::initialize();

            try
            {
                $window->resumeLayout();

                while (self::$running)
                {
                    $key = ncurses_wgetch($window->getUnderlyingWindow());

                    if ($key === -1)
                    {
                        usleep(250000);
                        continue;
                    }

                    logstr('Event registered: ', $key);

                    if ($key === NCURSES_KEY_MOUSE)
                    {
                        if (!ncurses_getmouse($mevent))
                        {
                            logstr('> Mouse event registered - mask: ', $mevent['mmask']);
                            $window->processMessage(MSG_MOUSE_EVENT, new MouseClickEvent($mevent['id'], $mevent['x'], $mevent['y'], $mevent['mmask']));
                        }
                    }
                    else
                    {
                        logstr('> Key event registered: ', $key);
                        $window->processMessage(MSG_KEY_EVENT, new KeyEvent($key));
                    }
                }
            }
            catch (\Throwable $t)
            {
                errorDump($t);
            }
        }

        public static function stop()
        {
            logstr('Stopping WindowManager');
            self::$running = false;
        }
    }
}
