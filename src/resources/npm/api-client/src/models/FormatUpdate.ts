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
import type { FormatTranslation } from './FormatTranslation';
import {
    FormatTranslationFromJSON,
    FormatTranslationFromJSONTyped,
    FormatTranslationToJSON,
} from './FormatTranslation';

/**
 * 
 * @export
 * @interface FormatUpdate
 */
export interface FormatUpdate {
    /**
     * 
     * @type {string}
     * @memberof FormatUpdate
     */
    worldId?: string;
    /**
     * 
     * @type {string}
     * @memberof FormatUpdate
     */
    type?: string;
    /**
     * 
     * @type {Array<FormatTranslation>}
     * @memberof FormatUpdate
     */
    translations: Array<FormatTranslation>;
}

/**
 * Check if a given object implements the FormatUpdate interface.
 */
export function instanceOfFormatUpdate(value: object): boolean {
    let isInstance = true;
    isInstance = isInstance && "translations" in value;

    return isInstance;
}

export function FormatUpdateFromJSON(json: any): FormatUpdate {
    return FormatUpdateFromJSONTyped(json, false);
}

export function FormatUpdateFromJSONTyped(json: any, ignoreDiscriminator: boolean): FormatUpdate {
    if ((json === undefined) || (json === null)) {
        return json;
    }
    return {
        
        'worldId': !exists(json, 'worldId') ? undefined : json['worldId'],
        'type': !exists(json, 'type') ? undefined : json['type'],
        'translations': ((json['translations'] as Array<any>).map(FormatTranslationFromJSON)),
    };
}

export function FormatUpdateToJSON(value?: FormatUpdate | null): any {
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

