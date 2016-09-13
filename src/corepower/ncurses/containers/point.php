<?php
namespace CorePower\Ncurses\Containers
{
    use CorePower\Ncurses\Events\PointChanged;

    class Point
    {
        use PointChanged;

        protected $y;
        protected $x;

        public function __construct(int $y, int $x)
        {
            $this->setY($y);
            $this->setX($x);
        }

        public function getY() : int
        {
            return $this->y;
        }

        public function setY(int $y)
        {
            if ($y === $this->y)
                return;

            $this->y = $y;
            $this->pointChanged();
        }

        public function getX() : int
        {
            return $this->x;
        }

        public function setX(int $x)
        {
            if ($x === $this->x)
                return;

            $this->x = $x;
            $this->pointChanged();
        }

        public function equals(Point $p) : bool
        {
            return $this->getY() === $p->getY() && $this->getX() === $p->getX();
        }

        public function __toString()
        {
            return '{Point: y=' . $this->getY() . ', x=' . $this->getX() . '}';
        }

        public static function current($window) : Point
        {
            $top = $left = 0;
            ncurses_getyx($window, $top, $left);
            return new Point($top, $left);
        }
    }
}
