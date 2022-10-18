/* tslint:disable */
/* eslint-disable */
/**
 * BMLT - OpenAPI 3.0
 * BMLT Admin API Documentation
 *
 * The version of the OpenAPI document: 1.0.0
 * 
 *
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

import { exists, mapValues } from '../runtime';
/**
 * 
 * @export
 * @interface Meeting
 */
export interface Meeting {
    /**
     * 
     * @type {number}
     * @memberof Meeting
     */
    serviceBodyId?: number;
    /**
     * 
     * @type {Array<number>}
     * @memberof Meeting
     */
    formatIds?: Array<number>;
    /**
     * 
     * @type {number}
     * @memberof Meeting
     */
    venueType?: number;
    /**
     * 
     * @type {boolean}
     * @memberof Meeting
     */
    temporarilyVirtual?: boolean;
    /**
     * 
     * @type {number}
     * @memberof Meeting
     */
    day?: number;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    startTime?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    duration?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    timeZone?: string;
    /**
     * 
     * @type {number}
     * @memberof Meeting
     */
    latitude?: number;
    /**
     * 
     * @type {number}
     * @memberof Meeting
     */
    longitude?: number;
    /**
     * 
     * @type {boolean}
     * @memberof Meeting
     */
    published?: boolean;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    email?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    worldId?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    name?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    locationText?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    locationInfo?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    locationStreet?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    locationNeighborhood?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    locationCitySubsection?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    locationMunicipality?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    locationSubProvince?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    locationProvince?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    locationPostalCode1?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    locationNation?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    phoneMeetingNumber?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    virtualMeetingLink?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    virtualMeetingAdditionalInfo?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    contactName1?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    contactName2?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    contactPhone1?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    contactPhone2?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    contactEmail1?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    contactEmail2?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    busLines?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    trainLine?: string;
    /**
     * 
     * @type {string}
     * @memberof Meeting
     */
    comments?: string;
    /**
     * 
     * @type {number}
     * @memberof Meeting
     */
    id?: number;
}

/**
 * Check if a given object implements the Meeting interface.
 */
export function instanceOfMeeting(value: object): boolean {
    let isInstance = true;

    return isInstance;
}

export function MeetingFromJSON(json: any): Meeting {
    return MeetingFromJSONTyped(json, false);
}

export function MeetingFromJSONTyped(json: any, ignoreDiscriminator: boolean): Meeting {
    if ((json === undefined) || (json === null)) {
        return json;
    }
    return {
        
        'serviceBodyId': !exists(json, 'serviceBodyId') ? undefined : json['serviceBodyId'],
        'formatIds': !exists(json, 'formatIds') ? undefined : json['formatIds'],
        'venueType': !exists(json, 'venueType') ? undefined : json['venueType'],
        'temporarilyVirtual': !exists(json, 'temporarilyVirtual') ? undefined : json['temporarilyVirtual'],
        'day': !exists(json, 'day') ? undefined : json['day'],
        'startTime': !exists(json, 'startTime') ? undefined : json['startTime'],
        'duration': !exists(json, 'duration') ? undefined : json['duration'],
        'timeZone': !exists(json, 'timeZone') ? undefined : json['timeZone'],
        'latitude': !exists(json, 'latitude') ? undefined : json['latitude'],
        'longitude': !exists(json, 'longitude') ? undefined : json['longitude'],
        'published': !exists(json, 'published') ? undefined : json['published'],
        'email': !exists(json, 'email') ? undefined : json['email'],
        'worldId': !exists(json, 'worldId') ? undefined : json['worldId'],
        'name': !exists(json, 'name') ? undefined : json['name'],
        'locationText': !exists(json, 'location_text') ? undefined : json['location_text'],
        'locationInfo': !exists(json, 'location_info') ? undefined : json['location_info'],
        'locationStreet': !exists(json, 'location_street') ? undefined : json['location_street'],
        'locationNeighborhood': !exists(json, 'location_neighborhood') ? undefined : json['location_neighborhood'],
        'locationCitySubsection': !exists(json, 'location_city_subsection') ? undefined : json['location_city_subsection'],
        'locationMunicipality': !exists(json, 'location_municipality') ? undefined : json['location_municipality'],
        'locationSubProvince': !exists(json, 'location_sub_province') ? undefined : json['location_sub_province'],
        'locationProvince': !exists(json, 'location_province') ? undefined : json['location_province'],
        'locationPostalCode1': !exists(json, 'location_postal_code_1') ? undefined : json['location_postal_code_1'],
        'locationNation': !exists(json, 'location_nation') ? undefined : json['location_nation'],
        'phoneMeetingNumber': !exists(json, 'phone_meeting_number') ? undefined : json['phone_meeting_number'],
        'virtualMeetingLink': !exists(json, 'virtual_meeting_link') ? undefined : json['virtual_meeting_link'],
        'virtualMeetingAdditionalInfo': !exists(json, 'virtual_meeting_additional_info') ? undefined : json['virtual_meeting_additional_info'],
        'contactName1': !exists(json, 'contact_name_1') ? undefined : json['contact_name_1'],
        'contactName2': !exists(json, 'contact_name_2') ? undefined : json['contact_name_2'],
        'contactPhone1': !exists(json, 'contact_phone_1') ? undefined : json['contact_phone_1'],
        'contactPhone2': !exists(json, 'contact_phone_2') ? undefined : json['contact_phone_2'],
        'contactEmail1': !exists(json, 'contact_email_1') ? undefined : json['contact_email_1'],
        'contactEmail2': !exists(json, 'contact_email_2') ? undefined : json['contact_email_2'],
        'busLines': !exists(json, 'bus_lines') ? undefined : json['bus_lines'],
        'trainLine': !exists(json, 'train_line') ? undefined : json['train_line'],
        'comments': !exists(json, 'comments') ? undefined : json['comments'],
        'id': !exists(json, 'id') ? undefined : json['id'],
    };
}

export function MeetingToJSON(value?: Meeting | null): any {
    if (value === undefined) {
        return undefined;
    }
    if (value === null) {
        return null;
    }
    return {
        
        'serviceBodyId': value.serviceBodyId,
        'formatIds': value.formatIds,
        'venueType': value.venueType,
        'temporarilyVirtual': value.temporarilyVirtual,
        'day': value.day,
        'startTime': value.startTime,
        'duration': value.duration,
        'timeZone': value.timeZone,
        'latitude': value.latitude,
        'longitude': value.longitude,
        'published': value.published,
        'email': value.email,
        'worldId': value.worldId,
        'name': value.name,
        'location_text': value.locationText,
        'location_info': value.locationInfo,
        'location_street': value.locationStreet,
        'location_neighborhood': value.locationNeighborhood,
        'location_city_subsection': value.locationCitySubsection,
        'location_municipality': value.locationMunicipality,
        'location_sub_province': value.locationSubProvince,
        'location_province': value.locationProvince,
        'location_postal_code_1': value.locationPostalCode1,
        'location_nation': value.locationNation,
        'phone_meeting_number': value.phoneMeetingNumber,
        'virtual_meeting_link': value.virtualMeetingLink,
        'virtual_meeting_additional_info': value.virtualMeetingAdditionalInfo,
        'contact_name_1': value.contactName1,
        'contact_name_2': value.contactName2,
        'contact_phone_1': value.contactPhone1,
        'contact_phone_2': value.contactPhone2,
        'contact_email_1': value.contactEmail1,
        'contact_email_2': value.contactEmail2,
        'bus_lines': value.busLines,
        'train_line': value.trainLine,
        'comments': value.comments,
        'id': value.id,
    };
}

