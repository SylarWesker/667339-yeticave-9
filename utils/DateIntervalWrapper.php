<?php

// Добавил метод, но теперь нужно будет прокидывать наружу св-ва и методы DateInterval (если они понадобятся) 
// плюс в DateInterval были свойства, а станут методами
// не очень удобно
class DateIntervalWrapper
{
    private DateInterval $date_interval;

    function __construct($date_interval)
    {
        $this->date_interval = $date_interval;
    }

    // public h()
    // {
    //     return $date_interval->h;
    // }

    public function is_equal_or_less_hour() {
        // тут по идее нужно чтобы все было нулем (миллисекунды не буду учитывать)
        $only_hours_has = $date_interval->y === 0 && 
                          $date_interval->m === 0 && 
                          $date_interval->d === 0;

        $one_hour = $date_interval->h === 1 && $date_interval->i === 0 && $date_interval->s === 0;
        $hour_or_less = $one_hour || $date_interval->h === 0;

        return $only_hours_has && $hour_or_less;
    }
}