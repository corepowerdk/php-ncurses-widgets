<?php
namespace CorePower\Ncurses\Widgets
{
    class RadioButton extends CheckBox
    {
        protected $groupId;

        public function __construct(int $groupId, string $label = '', bool $labelBeforeCheckBox = false)
        {
            parent::__construct(false, $label, $labelBeforeCheckBox);
            $this->groupId = $groupId;
            RadioButtonContainer::attach($this);

            $this->startChar = '(';
            $this->endChar = ')';
            $this->filledChar = '0';
        }

        public function getGroupId() : int
        {
            return $this->groupId;
        }

        public function getChecked() : bool
        {
            return $this->checked;
        }

        public function setChecked(bool $checked)
        {
            $this->setCheckedInternal($checked, true);
        }

        protected function setCheckedInternal(bool $checked, bool $checkRelated = true)
        {
            parent::setChecked($checked);
            if ($checked && $checkRelated)
            {
                foreach (RadioButtonContainer::getRelatedRadioButtons($this) as $relatedButtons)
                    $relatedButtons->setChecked(false, false);
            }
        }
    }

    class RadioButtonContainer
    {
        protected static $groups = [];

        public static function attach(RadioButton $radioButton)
        {
            $id = $radioButton->getGroupId();
            if (!array_key_exists($id, self::$groups))
                self::$groups[$id] = [];
            self::$groups[$id][] = $radioButton;
        }

        public static function detach(RadioButton $radioButton)
        {
            $id = $radioButton->getGroupId();
            if (array_key_exists($id, self::$groups) && in_array($radioButton, self::$groups[$id], true))
                unset(self::$groups[$id][$radioButton]);
        }

        public static function getRelatedRadioButtons(RadioButton $radioButton) : array
        {
            $id = $radioButton->getGroupId();
            if (!array_key_exists($id, self::$groups))
                return [];

            $related = [];
            /** @noinspection ForeachSourceInspection */
            foreach (self::$groups[$id] as $button)
                if ($button !== $radioButton)
                    $related[] = $button;

            return $related;
        }
    }
}