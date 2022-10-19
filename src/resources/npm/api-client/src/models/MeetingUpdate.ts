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
 * @interface MeetingUpdate
 */
export interface MeetingUpdate {
    /**
     * 
     * @type {number}
     * @memberof MeetingUpdate
     */
    serviceBodyId: number;
    /**
     * 
     * @type {Array<number>}
     * @memberof MeetingUpdate
     */
    formatIds: Array<number>;
    /**
     * 
     * @type {number}
     * @memberof MeetingUpdate
     */
    venueType: number;
    /**
     * 
     * @type {boolean}
     * @memberof MeetingUpdate
     */
    temporarilyVirtual?: boolean;
    /**
     * 
     * @type {number}
     * @memberof MeetingUpdate
     */
    day: number;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    startTime: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    duration: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    timeZone?: string;
    /**
     * 
     * @type {number}
     * @memberof MeetingUpdate
     */
    latitude: number;
    /**
     * 
     * @type {number}
     * @memberof MeetingUpdate
     */
    longitude: number;
    /**
     * 
     * @type {boolean}
     * @memberof MeetingUpdate
     */
    published: boolean;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    email?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    worldId?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    name: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    locationText?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    locationInfo?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    locationStreet?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    locationNeighborhood?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    locationCitySubsection?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    locationMunicipality?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    locationSubProvince?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    locationProvince?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    locationPostalCode1?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    locationNation?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    phoneMeetingNumber?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    virtualMeetingLink?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    virtualMeetingAdditionalInfo?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    contactName1?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    contactName2?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    contactPhone1?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    contactPhone2?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    contactEmail1?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    contactEmail2?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    busLines?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    trainLine?: string;
    /**
     * 
     * @type {string}
     * @memberof MeetingUpdate
     */
    comments?: string;
}

/**
 * Check if a given object implements the MeetingUpdate interface.
 */
export function instanceOfMeetingUpdate(value: object): boolean {
    let isInstance = true;
    isInstance = isInstance && "serviceBodyId" in value;
    isInstance = isInstance && "formatIds" in value;
    isInstance = isInstance && "venueType" in value;
    isInstance = isInstance && "day" in value;
    isInstance = isInstance && "startTime" in value;
    isInstance = isInstance && "duration" in value;
    isInstance = isInstance && "latitude" in value;
    isInstance = isInstance && "longitude" in value;
    isInstance = isInstance && "published" in value;
    isInstance = isInstance && "name" in value;

    return isInstance;
}

export function MeetingUpdateFromJSON(json: any): MeetingUpdate {
    return MeetingUpdateFromJSONTyped(json, false);
}

export function MeetingUpdateFromJSONTyped(json: any, ignoreDiscriminator: boolean): MeetingUpdate {
    if ((json === undefined) || (json === null)) {
        return json;
    }
    return {
        
        'serviceBodyId': json['serviceBodyId'],
        'formatIds': json['formatIds'],
        'venueType': json['venueType'],
        'temporarilyVirtual': !exists(json, 'temporarilyVirtual') ? undefined : json['temporarilyVirtual'],
        'day': json['day'],
        'startTime': json['startTime'],
        'duration': json['duration'],
        'timeZone': !exists(json, 'timeZone') ? undefined : json['timeZone'],
        'latitude': json['latitude'],
        'longitude': json['longitude'],
        'published': json['published'],
        'email': !exists(json, 'email') ? undefined : json['email'],
        'worldId': !exists(json, 'worldId') ? undefined : json['worldId'],
        'name': json['name'],
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
    };
}

export function MeetingUpdateToJSON(value?: MeetingUpdate | null): any {
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
    };
}
