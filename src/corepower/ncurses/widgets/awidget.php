<?php
namespace CorePower\Ncurses\Widgets
{
    use CorePower\Ncurses\Containers\Point;
    use CorePower\Ncurses\Containers\Size;
    use CorePower\Ncurses\Events\IPointChanged;
    use CorePower\Ncurses\Events\ISizeChanged;
    use CorePower\Ncurses\Events\PointChanged;
    use CorePower\Ncurses\Events\SizeChanged;

    abstract class AWidget implements IWidget, IPointChanged, ISizeChanged
    {
        use SizeChanged;
        use PointChanged;

        protected $size;
        protected $location;
        protected $name = '';
        protected $widgetType;

        /** @var Panel $parent */
        protected $parent;

        public function processMessage(string $message, $obj) : int
        {
            // Placeholder - to be overridden in sub classes if required
            return 0;
        }

        public function refresh()
        {
            if ($this->getParent() !== null)
                $this->getParent()->refresh();
        }

        public function getUnderlyingWindow()
        {
            return $this->getParent()->getUnderlyingWindow();
        }

        public function getName() : string
        {
            return $this->name;
        }

        public function setName(string $name)
        {
            $this->name = $name;
        }

        public function getLocation() : Point
        {
            if ($this->location === null)
            {
                $this->location = new Point(0, 0);
                $this->location->onPointChanged(function ()
                {
                    $this->pointChanged();
                });
            }
            return $this->location;
        }

        public function setLocation(Point $location)
        {
            if ($location === $this->location)
                return;

            $location->onPointChanged(function ()
            {
                $this->pointChanged();
            });

            $this->location = $location;
            $this->pointChanged();
        }

        public function getSize() : Size
        {
            if ($this->size === null)
            {
                $this->size = new Size(0, 0);
                $this->size->onSizeChanged(function ()
                {
                    $this->sizeChanged();
                });
            }
            return $this->size;
        }

        protected function setSize(Size $size)
        {
            if ($size === $this->size)
                return;

            $size->onSizeChanged(function ()
            {
                $this->sizeChanged();
            });
            $this->size = $size;
            $this->sizeChanged();
        }

        public function getWidth() : int
        {
            return $this->getSize()->getWidth();
        }

        public function getHeight() : int
        {
            return $this->getSize()->getHeight();
        }

        public function getTop() : int
        {
            return $this->getLocation()->getY();
        }

        public function getLeft() : int
        {
            return $this->getLocation()->getX();
        }

        public function getOffsetTop() : int
        {
            return $this->getTop() + $this->getHeight();
        }

        public function getOffsetLeft() : int
        {
            return $this->getLeft() + $this->getWidth();
        }

        public function getParent() : Panel
        {
            return $this->parent;
        }

        public function setParent(Panel $parent)
        {
            return $this->parent = $parent;
        }

        public function getWidgetType() : string
        {
            if ($this->widgetType === null)
            {
                $tmp = explode("\\", get_called_class());
                $this->widgetType = end($tmp);
            }
            return $this->widgetType;
        }

        public function __toString()
        {
            return '{Widget ' . $this->getWidgetType() . ', name = "' . $this->getName() . '"}';
        }
    }
}
