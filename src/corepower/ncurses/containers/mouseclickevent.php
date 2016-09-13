<?php
namespace CorePower\Ncurses\Containers
{
    class MouseClickEvent
    {
        protected $id;
        protected $x;
        protected $y;
        protected $mask;

        public function __construct(int $id, int $x, int $y, int $mask)
        {
            $this->id = $id;
            $this->x = $x;
            $this->y = $y;
            $this->mask = $mask;
        }

        public function getId() : int
        {
            return $this->id;
        }

        public function getX() : int
        {
            return $this->x;
        }

        public function getY() : int
        {
            return $this->y;
        }

        public function getMask() : int
        {
            return $this->mask;
        }

        public function button1Pressed() : bool
        {
            return $this->mask & NCURSES_BUTTON1_PRESSED;
        }

        public function button2Pressed() : bool
        {
            return $this->mask & NCURSES_BUTTON2_PRESSED;
        }

        public function button3Pressed() : bool
        {
            return $this->mask & NCURSES_BUTTON3_PRESSED;
        }

        public function button4Pressed() : bool
        {
            return $this->mask & NCURSES_BUTTON4_PRESSED;
        }

        public function buttonPressed() : bool
        {
            return $this->button1Pressed() || $this->button2Pressed() || $this->button3Pressed() || $this->button4Pressed();
        }

        public function button1Clicked() : bool
        {
            return $this->mask & NCURSES_BUTTON1_CLICKED;
        }

        public function button2Clicked() : bool
        {
            return $this->mask & NCURSES_BUTTON2_CLICKED;
        }

        public function button3Clicked() : bool
        {
            return $this->mask & NCURSES_BUTTON3_CLICKED;
        }

        public function button4Clicked() : bool
        {
            return $this->mask & NCURSES_BUTTON4_CLICKED;
        }

        public function buttonClicked():bool
        {
            return $this->button1Clicked() || $this->button2Clicked() || $this->button3Clicked() || $this->button4Clicked();
        }

        public function button1Released() : bool
        {
            return $this->mask & NCURSES_BUTTON1_RELEASED;
        }

        public function button2Released() : bool
        {
            return $this->mask & NCURSES_BUTTON2_RELEASED;
        }

        public function button3Released() : bool
        {
            return $this->mask & NCURSES_BUTTON3_RELEASED;
        }

        public function button4Released() : bool
        {
            return $this->mask & NCURSES_BUTTON4_RELEASED;
        }

        public function buttonReleased(): bool
        {
            return $this->button1Released() || $this->button2Released() || $this->button3Released() || $this->button4Released();
        }

        public function button1DoubleClicked():  bool
        {
            return $this->mask & NCURSES_BUTTON1_DOUBLE_CLICKED;
        }

        public function button2DoubleClicked():  bool
        {
            return $this->mask & NCURSES_BUTTON2_DOUBLE_CLICKED;
        }

        public function button3DoubleClicked():  bool
        {
            return $this->mask & NCURSES_BUTTON3_DOUBLE_CLICKED;
        }

        public function button4DoubleClicked():  bool
        {
            return $this->mask & NCURSES_BUTTON4_DOUBLE_CLICKED;
        }

        public function buttonDoubleClicked() : bool
        {
            return $this->button1DoubleClicked() || $this->button2DoubleClicked() || $this->button3DoubleClicked() || $this->button4DoubleClicked();
        }

        public function __toString() : string
        {
            return '{' . $this->id . ': ' . $this->x . ', ' . $this->y . ' - ' . $this->mask . '}';
        }
    }
}