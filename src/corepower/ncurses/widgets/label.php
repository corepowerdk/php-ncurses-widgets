<?php
namespace CorePower\Ncurses\Widgets
{
    use CorePower\Ncurses\Containers\Point;
    use CorePower\Ncurses\Containers\Size;

    class Label extends AWidget
    {
        protected $text;
        protected $multiline = false;

        public function __construct(string $text = '')
        {
            $this->text = $text;
            $this->getSize()->setWidth(strlen($this->text));
            $this->getSize()->setHeight(1);
        }

        public function getMultiline() : bool
        {
            return $this->multiline;
        }

        public function setMultiline(bool $multiline)
        {
            $this->multiline = $multiline;
            $this->calculateSize();
        }

        protected function calculateSize()
        {
            if ($this->multiline)
            {
                $lines = explode("\n", $this->getText());
                $height = count($lines);
                $width = 0;
                foreach ($lines as $line)
                {
                    $lineWidth = strlen($line);
                    if ($width < $lineWidth)
                        $width = $lineWidth;
                }

                $this->setSize(new Size($width, $height));
            }
            else
            {
                $this->getSize()->setWidth(strlen($this->text));
            }
        }

        public function draw()
        {
            logstr('drawing label text<', str_replace("\n", "\\n", $this->text), '> size<', $this->getSize(), '> location<', $this->getLocation(), '>');

            if ($this->multiline)
            {
                $lines = explode("\n", $this->text);
                $lineCount = count($lines);
                for ($i = 0; $i < $lineCount; $i++)
                    ncurses_mvwaddstr($this->getUnderlyingWindow(), $this->getTop() + $i, $this->getLeft(), $lines[$i]);
            }
            else
            {
                ncurses_mvwaddstr($this->getUnderlyingWindow(), $this->getTop(), $this->getLeft(), str_replace("\n", "\\n", $this->text));
            }
        }

        public function getText() : string
        {
            return $this->text;
        }

        public function setText(string $text)
        {
            $currentPoint = Point::current($this->getUnderlyingWindow());
            $this->text = $text;
            $this->calculateSize();
            ncurses_wmove($this->getUnderlyingWindow(), $currentPoint->getY(), $currentPoint->getX());
        }

        public function appendText(string $text)
        {
            $this->setText($this->getText() . $text);
        }
    }

}
