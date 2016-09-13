<?php
namespace CorePower\Ncurses\Widgets
{
    use CorePower\Ncurses\Containers\KeyEvent;
    use CorePower\Ncurses\Containers\Point;
    use CorePower\Ncurses\Containers\Rectangle;
    use CorePower\Ncurses\Containers\Size;
    use CorePower\Ncurses\Events\IPointChanged;
    use CorePower\Ncurses\Events\ISizeChanged;
    use CorePower\Ncurses\Shared\Colors;
    use CorePower\Ncurses\Shared\Cursor;
    use CorePower\Ncurses\WindowManager;

    class Panel extends AFocusable
    {
        use Cursor;

        protected $window;
        protected $decorationWindow;
        protected $border;
        protected $title;
        protected $viewportArea;
        protected $autoWidth = false;
        protected $autoHeight = false;
        protected $currentFocusIndex = 0;
        protected $layoutSuspended = true;
        protected $offset1 = 0;
        protected $offset2 = 0;

        /** @var IWidget[] $widgets */
        protected $widgets = [];

        /** @var IFocusable[] $focusableWidgets */
        protected $focusableWidgets = [];

        /** @var Scrollbar $verticalScrollbar */
        protected $verticalScrollbar;

        public function __construct(int $height = WIDGET_FILL, int $width = WIDGET_FILL, bool $border = false)
        {
            if ($height === WIDGET_FILL)
                $this->autoHeight = true;

            if ($width === WIDGET_FILL)
                $this->autoWidth = true;

            $this->size = new Size($width, $height);
            $this->viewportArea = new Rectangle(0, 0, 1, 1);
            $this->border = $border;

            $this->recreateWindow();

            logstr('init panel height<', $this->getHeight(), '> width<', $this->getWidth(), '>');

            $this->offset1 = $this->border ? 1 : 0;
            $this->offset2 = $this->border ? 2 : 0;

            $this->verticalScrollbar = new VerticalScrollbar($this);
        }

        public function getUnderlyingWindow()
        {
            return $this->window;
        }

        public function getDecorationWindow()
        {
            return $this->decorationWindow;
        }

        public function getBorder() : bool
        {
            return $this->border;
        }

        public function getTitle() : string
        {
            return $this->title;
        }

        public function setTitle(string $title)
        {
            $this->title = $title;
            $this->draw();
        }

        public function getViewportArea() : Rectangle
        {
            return $this->viewportArea;
        }

        public function getclientArea() : Rectangle
        {
            return new Rectangle($this->getTop() + $this->offset1, $this->getLeft() + $this->offset1, $this->getHeight() - $this->offset2, $this->getWidth() - $this->offset2);
        }

        public function processMessage(string $message, $obj) : int
        {
            $return = 0;

            if (count($this->focusableWidgets) > 0)
                $return = $this->focusableWidgets[$this->currentFocusIndex]->processMessage($message, $obj);

            if ($return !== EVENT_HANDLED)
            {
                switch ($message)
                {
                    case MSG_KEY_EVENT:
                        /** @var $obj KeyEvent */
                        switch ($obj->getKey())
                        {
                            case NCURSES_KEY_RESIZE:
                                $this->draw();
                                return EVENT_HANDLED;
                            case NCURSES_KEY_UP:
                            case NCURSES_KEY_BTAB:
                                $this->setPreviousFocus();
                                return EVENT_HANDLED;
                            case NCURSES_KEY_TAB:
                            case NCURSES_KEY_DOWN:
                                $this->setNextFocus();
                                return EVENT_HANDLED;
                            case NCURSES_KEY_ESCAPE:
                                WindowManager::stop();
                                return EVENT_HANDLED;
                        }
                        break;

                    case MSG_MOUSE_EVENT:
                        break;
                }
            }

            return $return;
        }

        protected function recreateWindow()
        {
            if ($this->window !== null)
                ncurses_wclear($this->window);

            if ($this->decorationWindow !== null)
                ncurses_wclear($this->decorationWindow);

            logstr('size before recreating window: ', $this->getSize());

            if ($this->autoHeight || $this->autoWidth)
            {
                // Create a new window and destroy it again
                // after extracting the max X and Y values
                $win = ncurses_newwin(0, 0, $this->getLeft(), $this->getTop());
                $maxY = 0;
                $maxX = 0;
                ncurses_getmaxyx($win, $maxY, $maxX);
                ncurses_wclear($win);

                if ($this->autoHeight)
                    $this->getSize()->setHeight($maxY - $this->getTop() - 1);

                if ($this->autoWidth)
                    $this->getSize()->setWidth($maxX - $this->getLeft() - 1);
            }

            $viewportArea = $this->getViewportArea();

            logstr('size after recreating window: ', $this->getSize());

            logstr('Creating panel win, viewport rect: ', $viewportArea);
            $this->window = ncurses_newpad($viewportArea->getHeight(), $viewportArea->getWidth());
            $this->decorationWindow = ncurses_newpad($this->getHeight() + 1, $this->getWidth() + 1);
        }

        public function draw()
        {
            if ($this->layoutSuspended)
                return;

            // Update the view port size (in case of widget resize)
            foreach ($this->widgets as $widget)
                $this->updateViewportArea($widget);

            $this->recreateWindow();

            if ($this->border)
            {
                ncurses_wborder($this->decorationWindow, 0, 0, 0, 0, 0, 0, 0, 0);

                if ($this->title !== null && $this->title !== '')
                {
                    ncurses_wattron($this->decorationWindow, NCURSES_A_BOLD);
                    Colors::setColor($this->decorationWindow, Colors::YELLOW_BLACK);
                    ncurses_mvwaddstr($this->decorationWindow, 0, 2, ' ' . substr($this->title, 0, $this->getWidth() - 5) . ' ');
                    Colors::reset($this->decorationWindow);
                    ncurses_wattroff($this->decorationWindow, NCURSES_A_BOLD);
                }
            }

            ncurses_wmove($this->window, 0, 0);

            foreach ($this->focusableWidgets as $widget)
                $this->setWidgetFocus($widget, false);

            foreach ($this->widgets as $widget)
            {
                ncurses_wmove($this->getUnderlyingWindow(), $widget->getLocation()->getY(), $widget->getLocation()->getX());
                $widget->draw();
            }

            $this->verticalScrollbar->draw();

            if (count($this->focusableWidgets) > 0)
            {
                $this->showCursor();
                $this->setWidgetFocus($this->focusableWidgets[0], true);
            }
            else
            {
                $this->hideCursor();
            }

            $this->refresh();
        }

        protected function setWidgetFocus(IFocusable $widget, bool $setFocus)
        {
            ncurses_wmove($this->getUnderlyingWindow(), $widget->getLocation()->getY(), $widget->getLocation()->getX());
            $widget->setFocus($setFocus);

            $viewportArea = $this->getViewportArea();
            $area = $this->getclientArea();
            $refresh = false;
            if ($setFocus)
            {
                if ($area->getHeight() <= $widget->getLocation()->getY() - $viewportArea->getY())
                {
                    $viewportArea->setY($widget->getLocation()->getY() - $area->getHeight() + 1);
                    $refresh = true;
                }

                if ($widget->getLocation()->getY() < $viewportArea->getY())
                {
                    $viewportArea->setY($widget->getLocation()->getY());
                    $refresh = true;
                }
            }

            if ($refresh)
                $this->refresh();
        }

        public function refresh()
        {
            $this->verticalScrollbar->draw();

            if ($this->getBorder())
            {
                $decosminrow = $this->getTop();
                $decosmincol = $this->getLeft();
                $decosmaxrow = $this->getTop() + $this->getHeight();
                $decosmaxcol = $this->getLeft() + $this->getWidth();

                logstr('Panel decoration refreshed posY<0> posX<0> sminrow<', $decosminrow, '> smincol<', $decosmincol, '> smaxrow<', $decosmaxrow, '> smaxcol<', $decosmaxcol, '>');
                ncurses_prefresh($this->decorationWindow, 0, 0, $decosminrow, $decosmincol, $decosmaxrow, $decosmaxcol);
            }

            $this->refreshClientArea();
        }

        public function refreshClientArea()
        {
            $this->scrollTo($this->getViewportArea()->getPoint());
        }

        public function scrollTo(Point $point)
        {
            $viewportArea = $this->getViewportArea();

            $maxY = $viewportArea->getY() + $viewportArea->getHeight();
            $maxX = $viewportArea->getX() + $viewportArea->getWidth();

            if ($point->getY() > $maxY)
                $viewportArea->setY($maxY);
            else
                $viewportArea->setY($point->getY());

            if ($point->getX() > $maxX)
                $viewportArea->setX($maxX);
            else
                $viewportArea->setX($point->getX());

            $sminrow = $this->getTop() + $this->offset1;
            $smincol = $this->getLeft() + $this->offset1;
            $smaxrow = $this->getTop() + $this->getHeight() - $this->offset2;
            $smaxcol = $this->getLeft() + $this->getWidth() - $this->offset2;

            logstr('Panel refreshed viewport<', $viewportArea, '> posY<', $viewportArea->getY(), '> posX<', $viewportArea->getX(), '> sminrow<', $sminrow, '> smincol<', $smincol, '> smaxrow<', $smaxrow, '> smaxcol<', $smaxcol, '>');
            ncurses_prefresh($this->window, $viewportArea->getY(), $viewportArea->getX(), $sminrow, $smincol, $smaxrow, $smaxcol);
        }

        public function addWidget(IWidget $widget)
        {
            logstr('Adding widget: ', get_class($widget) . ', at top<', $widget->getLocation()->getY(), '> left<', $widget->getLocation()->getX(), '>');

            $widget->setParent($this);

            $this->widgets[] = $widget;
            $this->updateViewportArea($widget);

            // Redraw panel content if a widget changes location
            if ($widget instanceof IPointChanged)
            {
                $widget->onPointChanged(function ()
                {
                    $this->draw();
                });
            }

            // Redraw panel content if a widget changes size
            if ($widget instanceof ISizeChanged)
            {
                $widget->onSizeChanged(function ()
                {
                    $this->draw();
                });
            }

            if ($widget instanceof IFocusable)
            {
                // Find the correct index to use, so focusable widgets are always oredered by tab index
                /** @noinspection ForeachInvariantsInspection */
                for ($i = 0, $i_c = count($this->focusableWidgets); $i < $i_c; $i++)
                {
                    if ($this->focusableWidgets[$i]->getTabIndex() > $widget->getTabIndex())
                        break;
                }
                array_splice($this->focusableWidgets, $i, 0, [$widget]);
            }

            $this->draw();
        }

        protected function updateViewportArea(IWidget $widget)
        {
            $maxY = $widget->getLocation()->getY() + $widget->getSize()->getHeight();
            $maxX = $widget->getLocation()->getX() + $widget->getSize()->getWidth() + $this->offset2;

            $viewportArea = $this->getViewportArea();

            if ($maxY > $viewportArea->getHeight())
                $viewportArea->setHeight($maxY);

            if ($maxX > $viewportArea->getWidth())
                $viewportArea->setWidth($maxX);
        }

        public function setPreviousFocus()
        {
            $c_focusableWidgets = count($this->focusableWidgets);
            if ($c_focusableWidgets > 0)
            {
                $this->setWidgetFocus($this->focusableWidgets[$this->currentFocusIndex], false);
                if (--$this->currentFocusIndex < 0)
                    $this->currentFocusIndex = $c_focusableWidgets - 1;
                $this->setWidgetFocus($this->focusableWidgets[$this->currentFocusIndex], true);
            }
        }

        public function setNextFocus()
        {
            $c_focusableWidgets = count($this->focusableWidgets);
            if ($c_focusableWidgets > 0)
            {
                $this->setWidgetFocus($this->focusableWidgets[$this->currentFocusIndex], false);
                if (++$this->currentFocusIndex >= $c_focusableWidgets)
                    $this->currentFocusIndex = 0;
                $this->setWidgetFocus($this->focusableWidgets[$this->currentFocusIndex], true);
            }
        }

        protected function gotFocus()
        {
            $c_focusableWidgets = count($this->focusableWidgets);
            if ($c_focusableWidgets > 0)
                $this->setWidgetFocus($this->focusableWidgets[$this->currentFocusIndex], true);
        }

        protected function lostFocus()
        {
        }

        public function suspendLayout()
        {
            $this->layoutSuspended = true;
        }

        public function resumeLayout()
        {
            $this->layoutSuspended = false;
            $this->draw();
        }
    }

    /** @property Panel $parent */
    abstract class Scrollbar extends AWidget
    {
        protected $visible = false;
        protected $window;

        public function __construct(Panel $parent)
        {
            $this->parent = $parent;
            $this->window = $this->parent->getUnderlyingWindow();
        }

        public function isVisible() : bool
        {
            return $this->visible;
        }

        protected function drawBackground(int $top, int $left)
        {
            ncurses_wmove($this->parent->getDecorationWindow(), $top, $left);
            ncurses_waddch($this->parent->getDecorationWindow(), NCURSES_ACS_CKBOARD);
        }

        protected function drawMarker(int $top, int $left)
        {
            ncurses_wmove($this->parent->getDecorationWindow(), $top, $left);
            Colors::setColor($this->parent->getDecorationWindow(), Colors::RED_RED);
            ncurses_waddstr($this->parent->getDecorationWindow(), ' ');
            Colors::reset($this->parent->getDecorationWindow());
        }
    }

    /** @property Panel $parent */
    class VerticalScrollbar extends Scrollbar
    {
        public function draw()
        {
            $rect = $this->parent->getclientArea();
            $viewportArea = $this->parent->getViewportArea();

            // Calculate where to put the marker
            $total = $viewportArea->getHeight();
            $this->visible = ($rect->getHeight() - $total < 0);
            if (!$this->isVisible())
                return;

            // Draw the background of the scroll bar
            for ($i = $rect->getY(); $i <= $rect->getHeight(); $i++)
                $this->drawBackground($i, $rect->getWidth() + 1);

            $pos = round(($viewportArea->getY() * $rect->getHeight()) / ($total - $rect->getHeight()));
            if ($pos < $rect->getY())
                $pos = $rect->getY();
            else if ($pos > $rect->getHeight())
                $pos = $rect->getHeight();

            $this->drawMarker($pos, $rect->getWidth() + 1);
        }
    }

    /** @property Panel $parent */
    class HorizontalScrollbar extends Scrollbar
    {
        public function draw()
        {

        }
    }
}
