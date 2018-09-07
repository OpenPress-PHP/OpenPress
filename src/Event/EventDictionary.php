<?php
namespace OpenPress\Event;

/**
 * Defines all the names of events
 * Events can have three phrases:
 * - pre => Before the instance is created
 * - on => During the instance is being created
 * - post => After the instance has been created
 */
class EventDictionary
{
    /**
     * Event is called when routes need to be defined.
     */
    const ROUTE = "route.on";
}
