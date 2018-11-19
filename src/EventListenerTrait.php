<?php

namespace LoveCoding\CReceiver;

trait EventListenerTrait
{
	/*private $events = [
        'onload' => null,
        'onfinished' => null,
        'onprogress' => null,
        'onerror' => null
    ];*/
    private $events = [];

    /********************************************************************************
     * Setting listeners
     *******************************************************************************/
    /**
     * Get a listener by its event
     * @param  String $event Events from event list had declared
     * @return mixed         Return callable if event has exist, else
     */
    public function getEventListener(string $event)
    {
        if (isset($this->events[$event])) {
            return $this->events[$event];
        }

        return null;
    }

    /**
     * Add an event to event list
     * @param String   $event    Name of event must have in event list had declare
     * @param callable $callable The function for callback
     */
    public function addEventListener(string $event, callable $callable)
    {
        $this->events[$event] = $callable;
    }

    /**
     * Call callback of event
     * @param  String $event         Name of event
     * @param  mixed $eventCallback  The arguments will pass in callback of event
     * @return mixed                 Return of callback
     */
    public function callEventListener(string $event, $eventCallback = null)
    {
        $callable = $this->getEventListener($event);

        if ( is_callable($callable) ) {
            if ($eventCallback !== null)
                return $callable($eventCallback);
            else {
                return $callable();
            }
        }
    }
}