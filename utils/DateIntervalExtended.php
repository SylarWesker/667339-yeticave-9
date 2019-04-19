<?php

class DateIntervalExtended extends DateInterval
{
    function __construct($date_interval) {
        // Не работает!
        // $this = $date_interval;

        // Очень увлекательно (нет!)
        // Так явно никто не делает (а если полей будет сто?)
        // $this->y = $date_interval->y;
        // $this->m = $date_interval->m;
        // $this->d = $date_interval->d;
        // $this->h = $date_interval->h;
        // $this->i = $date_interval->i;
        // $this->s = $date_interval->s;
        // $this->f = $date_interval->f;
        // $this->invert = $date_interval->invert;
        // $this->days = $date_interval->days;

        // Вызвать конструктор предка. Ок. Но нужно будет сформировать строку для конструктора.
        // не сильно отличается от предыдущего варианта.
        // $interval_spec = "Y" . $date_interval->y . "M" . $date_interval->m;
        $interval_spec = $date_interval->format('P%yY%mM%dDT%hH%iM%sS');
        parent::__construct($interval_spec);
    }

    public function is_equal_or_less_hour() {
        // тут по идее нужно чтобы все было нулем (миллисекунды не буду учитывать)
        $only_hours_has = $this->y === 0 && 
                          $this->m === 0 && 
                          $this->d === 0;

        $one_hour = $this->h === 1 && $this->i === 0 && $this->s === 0;
        $hour_or_less = $one_hour || $this->h === 0;

        return $only_hours_has && $hour_or_less;
    }
}