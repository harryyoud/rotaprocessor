#!/usr/bin/env python

import json
import sys
from datetime import datetime
from itertools import zip_longest

import caldav, caldav.lib.error


def add_shift_to_calendar(shift, calendar, config, reuse_event=None):
    if shift is None:
        return None
    event = reuse_event
    if event is None:
        event = caldav.Event(
            calendar.client,
            data=caldav.vcal.create_ical(ical_fragment=None, objtype='VEVENT', ical_data={}),
            parent=calendar)
    set_or_add_attr(event, 'dtstart', datetime.strptime(shift['start'], '%Y-%m-%dT%H:%M:%S').replace(tzinfo=None))
    set_or_add_attr(event, 'dtend', datetime.strptime(shift['end'], '%Y-%m-%dT%H:%M:%S').replace(tzinfo=None))
    set_or_add_attr(event, 'summary', f"{config['prefix']}{shift['title']}")
    set_or_add_attr(event, 'color', config['color'])
    set_or_add_attr(event, 'categories', [config['category']])
    event.save()
    return event


def set_or_add_attr(event, attr_name, attr_value):
    try:
        attr = getattr(event.vobject_instance.vevent, attr_name)
        attr.value = attr_value
    except AttributeError:
        event.vobject_instance.vevent.add(attr_name).value = attr_value


def get_existing_shifts(calendar, configured_category):
    existing_shifts = []
    for event in calendar.events():
        if any(cat.value[0] == configured_category for cat in
               event.vobject_instance.vevent.contents['categories']):
            existing_shifts.append(event)
    return existing_shifts


def synchronise_events(calendar, existing_events, new_shifts, config):
    changed_events = 0
    for pair in zip_longest(existing_events, new_shifts, fillvalue=None):
        if pair[1] is None:
            pair[0].delete()
            continue
        data_before = None if pair[0] is None else pair[0].data
        event = add_shift_to_calendar(pair[1], calendar, config, reuse_event=pair[0])
        if not event.data == data_before:
            changed_events += 1
    return changed_events


def parse_and_add_to_calendar(config, shifts):
    out = {}
    with caldav.DAVClient(url=config['url'], username=config['username'],
                          password=config['password']) as client:
        calendar = client.calendar(url=config['url'])
        # print(" => Downloading existing events from your calendar...")
        try:
            existing_shifts = get_existing_shifts(calendar, config['category'])
        except caldav.lib.error.DAVError as e:
            out['error'] = {
                'type': e.__class__.__name__,
                'where': 'Trying to fetch existing events',
                'detail': e.url,
                'reason': e.reason,
            }
            return out
        # print(f" => Found {len(existing_shifts)} existing shift events, "
        #       f"repurposing these to avoid filling the trash")
        # if len(existing_shifts) > len(shifts):
        #     print(f" ** Warning: {len(existing_shifts) - len(shifts)} more shifts "
        #           f"on calendar than in (spreadsheet. These will be deleted")
        try:
            changed_events = synchronise_events(calendar, existing_shifts, shifts, config)
        except caldav.lib.error.DAVError as e:
            out['error'] = {
                'type': e.__class__.__name__,
                'where': 'Trying to synchronise new events',
                'detail': e.url,
                'reason': e.reason,
            }
            return out

    out['stats'] = {
        "before": len(existing_shifts),
        "after": len(shifts),
        "modified": changed_events,
    }
    return out


def main():
    input = json.load(sys.stdin, strict=False)
    result = parse_and_add_to_calendar(input['calendar'], input['shifts'])
    print(json.dumps(result))


if __name__ == '__main__':
    main()
