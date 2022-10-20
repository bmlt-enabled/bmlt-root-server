/* tslint:disable */
/* eslint-disable */
/**
 * BMLT
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
import type { FormatTranslation } from './FormatTranslation';
import {
    FormatTranslationFromJSON,
    FormatTranslationFromJSONTyped,
    FormatTranslationToJSON,
} from './FormatTranslation';

/**
 * 
 * @export
 * @interface FormatCreate
 */
export interface FormatCreate {
    /**
     * 
     * @type {string}
     * @memberof FormatCreate
     */
    worldId?: string;
    /**
     * 
     * @type {string}
     * @memberof FormatCreate
     */
    type?: string;
    /**
     * 
     * @type {Array<FormatTranslation>}
     * @memberof FormatCreate
     */
    translations: Array<FormatTranslation>;
}

/**
 * Check if a given object implements the FormatCreate interface.
 */
export function instanceOfFormatCreate(value: object): boolean {
    let isInstance = true;
    isInstance = isInstance && "translations" in value;

    return isInstance;
}

export function FormatCreateFromJSON(json: any): FormatCreate {
    return FormatCreateFromJSONTyped(json, false);
}

export function FormatCreateFromJSONTyped(json: any, ignoreDiscriminator: boolean): FormatCreate {
    if ((json === undefined) || (json === null)) {
        return json;
    }
    return {
        
        'worldId': !exists(json, 'worldId') ? undefined : json['worldId'],
        'type': !exists(json, 'type') ? undefined : json['type'],
        'translations': ((json['translations'] as Array<any>).map(FormatTranslationFromJSON)),
    };
}

export function FormatCreateToJSON(value?: FormatCreate | null): any {
    if (value === undefined) {
        return undefined;
    }
    if (value === null) {
        return null;
    }
    return {
        
        'worldId': value.worldId,
        'type': value.type,
        'translations': ((value.translations as Array<any>).map(FormatTranslationToJSON)),
    };
}

