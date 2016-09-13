<?php
namespace CorePower\Ncurses\Widgets
{
    use CorePower\Ncurses\Shared\Colors;

    class ProgressBar extends AWidget
    {
        protected $width;
        protected $min = 0;
        protected $max = 100;
        protected $value = 0;

        public function __construct(int $width = 10)
        {
            $this->width = $width;
        }

        public function setMinimum(int $min)
        {
            if ($min < 0)
                throw new \InvalidArgumentException('Parameter $min cannot be below 0');

            $this->min = $min;
        }

        public function getMinimum() : int
        {
            return $this->min;
        }

        public function setMaximum(int $max)
        {
            if ($max < 0)
                throw new \InvalidArgumentException('Parameter $max cannot be below 0');

            $this->max = $max;
        }

        public function getMaximum() : int
        {
            return $this->max;
        }

        public function setValue(int $value)
        {
            if ($this->value === $value)
                return;

            if ($value < $this->getMinimum())
                return;
            IF ($value > $this->getMaximum())
                return;

            $this->value = $value;
            $this->draw();
        }

        public function getValue() : int
        {
            return $this->value;
        }

        /* Negative values allowed */
        public function increment(int $value = 1)
        {
            $this->setValue($this->getValue() + $value);
        }

        public function draw()
        {
            if ($this->parent === null)
                return;

            ncurses_wmove($this->getUnderlyingWindow(), $this->getTop(), $this->getLeft());

            $progress_width = (int)($this->width * ($this->value - $this->min) / max($this->max - $this->min, 1));

            $text = (int)($this->value / $this->max * 100) . '%';
            $text_len = strlen($text);
            $text_pos_start = (int)(($this->width - $text_len) / 2);
            $text_pos_end = $text_pos_start + $text_len;
            logstr('perc<', $text, '> pos_start<', $text_pos_start, '> pos_end<', $text_pos_end, '>');

            for ($i = 0; $i < $this->width; $i++)
            {
                $color = ($i <= $progress_width ? Colors::WHITE_BLUE : Colors::WHITE_GRAY);
                Colors::setColor($this->getUnderlyingWindow(), $color);

                $str = ' ';
                if ($i >= $text_pos_start && $i < $text_pos_end)
                    /** @noinspection SubStrUsedAsArrayAccessInspection */
                    $str = substr($text, $i - $text_pos_end, 1);

                ncurses_waddstr($this->getUnderlyingWindow(), $str);
            }

            Colors::reset($this->getUnderlyingWindow());
        }
    }
}
