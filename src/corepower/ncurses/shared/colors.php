<?php
namespace CorePower\Ncurses\Shared
{
    class Colors
    {
        const WHITE_BLACK = 0;
        const RED_BLACK = 1;
        const GREEN_BLACK = 2;
        const YELLOW_BLACK = 3;
        const BLUE_BLACK = 4;
        const CYAN_BLACK = 5;
        const MAGENTA_BLACK = 6;
        const WHITE_BLUE = 7;
        const WHITE_WHITE = 8;
        const RED_RED = 9;
        const WHITE_GRAY = 10;

        public static function initialize()
        {
            if (ncurses_has_colors())
            {
                ncurses_start_color();
                self::defineColors();
                ncurses_init_pair(self::WHITE_BLACK, NCURSES_COLOR_WHITE, NCURSES_COLOR_BLACK);
                ncurses_init_pair(self::RED_BLACK, NCURSES_COLOR_RED, NCURSES_COLOR_BLACK);
                ncurses_init_pair(self::GREEN_BLACK, NCURSES_COLOR_GREEN, NCURSES_COLOR_BLACK);
                ncurses_init_pair(self::YELLOW_BLACK, NCURSES_COLOR_YELLOW, NCURSES_COLOR_BLACK);
                ncurses_init_pair(self::BLUE_BLACK, NCURSES_COLOR_BLUE, NCURSES_COLOR_BLACK);
                ncurses_init_pair(self::CYAN_BLACK, NCURSES_COLOR_CYAN, NCURSES_COLOR_BLACK);
                ncurses_init_pair(self::MAGENTA_BLACK, NCURSES_COLOR_MAGENTA, NCURSES_COLOR_BLACK);
                ncurses_init_pair(self::WHITE_BLUE, NCURSES_COLOR_WHITE, NCURSES_COLOR_BLUE);
                ncurses_init_pair(self::WHITE_WHITE, NCURSES_COLOR_WHITE, NCURSES_COLOR_WHITE);
                ncurses_init_pair(self::RED_RED, NCURSES_COLOR_RED, NCURSES_COLOR_RED);
                ncurses_init_pair(self::WHITE_GRAY, NCURSES_COLOR_WHITE, NCURSES_COLOR_GRAY);
            }
        }

        protected static function defineColors()
        {
            $i = 7;
            // Gray
            define('NCURSES_COLOR_GRAY', ++$i);
            ncurses_init_color($i, 500, 500, 500);
            // ...
        }

        public static function reset($window)
        {
            if (ncurses_has_colors())
                ncurses_wcolor_set($window, self::WHITE_BLACK);
        }

        public static function setColor($window, $color)
        {
            if (ncurses_has_colors())
                ncurses_wcolor_set($window, $color);
        }
    }
}
