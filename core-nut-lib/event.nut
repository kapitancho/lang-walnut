module $event:

EventListener = ^Nothing => *Null;
EventBus = $[listeners: Array<EventListener>];
