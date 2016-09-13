<?php
namespace CorePower\Ncurses\Widgets
{
    use CorePower\Ncurses\Containers\KeyEvent;
    use CorePower\Ncurses\Containers\Size;
    use CorePower\Ncurses\Events\CheckedChanged;

    class CheckBox extends AFocusable
    {
        use CheckedChanged;

        protected $label;
        protected $checked;
        protected $labelBeforeCheckBox;

        protected $startChar = '[';
        protected $endChar = ']';
        protected $filledChar = 'X';
        protected $emptyChar = ' ';

        public function __construct(bool $checked = false, string $label = '', bool $labelBeforeCheckBox = false)
        {
            $this->checked = $checked;
            $this->labelBeforeCheckBox = $labelBeforeCheckBox;
            $this->label = $label;
            $this->size = new Size(strlen($label . $this->startChar . $this->endChar . $this->filledChar) + 1, 1);
        }

        public function getText() : string
        {
            return $this->label;
        }

        public function getChecked() : bool
        {
            return $this->checked;
        }

        public function setChecked(bool $checked)
        {
            if ($checked === $this->checked)
                return;

            $this->checked = $checked;
            $this->checkedChanged();
            $this->draw();
        }

        public function processMessage(string $message, $obj) : int
        {
            if (parent::processMessage($message, $obj) === EVENT_HANDLED)
                return EVENT_HANDLED;

            if ($message === MSG_KEY_EVENT)
            {
                /** @var $obj KeyEvent */
                if ($obj->getKey() === NCURSES_KEY_SPACEBAR)
                {
                    $this->setChecked(!$this->getChecked());
                    $this->setCursorInField();
                }
            }

            return 0;
        }

        public function draw()
        {
            logstr('drawing box', (new \Exception())->getTraceAsString());

            if ($this->labelBeforeCheckBox)
            {
                ncurses_waddstr($this->getUnderlyingWindow(), $this->label . ' ');
                $this->drawCheckBox();
            }
            else
            {
                $this->drawCheckBox();
                ncurses_waddstr($this->getUnderlyingWindow(), ' ' . $this->label);
            }
        }

        protected function drawCheckBox()
        {
            $offset = $this->labelBeforeCheckBox ? strlen($this->label) + 1 : 0;
            ncurses_mvwaddstr($this->getUnderlyingWindow(), $this->getTop(), $this->getLeft() + $offset, $this->startChar);
            ncurses_wattron($this->getUnderlyingWindow(), NCURSES_A_BOLD);
            ncurses_waddstr($this->getUnderlyingWindow(), $this->checked ? $this->filledChar : $this->emptyChar);
            ncurses_wattroff($this->getUnderlyingWindow(), NCURSES_A_BOLD);
            ncurses_waddstr($this->getUnderlyingWindow(), $this->endChar);
        }

        protected function gotFocus()
        {
            $this->setCursorInField();
        }

        protected function lostFocus()
        {
        }

        protected function setCursorInField()
        {
            $newX = ($this->labelBeforeCheckBox ? $this->getLeft() + strlen($this->label) + 2 : $this->getLeft() + 1);
            ncurses_wmove($this->getUnderlyingWindow(), $this->getTop(), $newX);
            $this->refresh();
        }
    }
}
