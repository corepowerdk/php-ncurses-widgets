<?php
namespace CorePower\Ncurses\Containers
{
    class Rectangle
    {
        protected $point;
        protected $size;

        public function __construct(int $y, int $x, int $height, int $width)
        {
            $this->point = new Point($y, $x);
            $this->size = new Size($width, $height);
        }

        public function getY() : int
        {
            return $this->getPoint()->getY();
        }

        public function setY(int $y)
        {
            $this->getPoint()->setY($y);
        }

        public function getX() : int
        {
            return $this->getPoint()->getX();
        }

        public function setX(int $x)
        {
            $this->getPoint()->setX($x);
        }

        public function getWidth() : int
        {
            return $this->getSize()->getWidth();
        }

        public function setWidth(int $width)
        {
            $this->getSize()->setWidth($width);
        }

        public function getHeight() : int
        {
            return $this->getSize()->getHeight();
        }

        public function setHeight(int $height)
        {
            $this->getSize()->setHeight($height);
        }

        public function getPoint() : Point
        {
            return $this->point;
        }

        public function getSize() : Size
        {
            return $this->size;
        }

        public function isEmpty() : bool
        {
            return ($this->getY() === 0 && $this->getX() === 0 && $this->getHeight() === 0 && $this->getWidth() === 0);
        }

        public static function getEmpty() : Rectangle
        {
            return new Rectangle(0, 0, 0, 0);
        }

        public function getIntersection(Rectangle $r) : Rectangle
        {
            $x1 = max($r->getX(), $this->getX());
            $x2 = min($r->getX() + $r->getWidth(), $this->getX() + $this->getWidth());
            $y1 = max($r->getY(), $this->getY());
            $y2 = min($r->getY() + $r->getHeight(), $this->getY() + $this->getHeight());

            if ($x2 >= $x1 && $y2 >= $y1)
                return new Rectangle($y1, $x1, $y2 - $y1, $x2 - $x1);

            return self::getEmpty();
        }

        public function equals(Rectangle $r) : bool
        {
            return $this->getSize()->equals($r->getSize()) && $this->getPoint()->equals($r->getPoint());
        }

        public function __toString()
        {
            return '{Rectangle: y=' . $this->getY() . ', x=' . $this->getX() . ', height=' . $this->getHeight() . ', width=' . $this->getWidth() . '}';
        }
    }
}
