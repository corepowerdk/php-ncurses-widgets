<?php
namespace CorePower\Ncurses\Widgets
{
    use CorePower\Ncurses\Containers\Point;
    use CorePower\Ncurses\Containers\Size;

    interface IWidget
    {
        public function getUnderlyingWindow();

        public function processMessage(string $message, $obj) : int;

        public function draw();

        public function refresh();

        public function getName() : string;

        public function setName(string $name);

        public function getSize() : Size;

        public function getLocation() : Point;

        public function setLocation(Point $location);

        public function setParent(Panel $parent);

        public function getParent() : Panel;

        public function __toString();
    }
}
