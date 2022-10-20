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
/**
 * 
 * @export
 * @interface ValidationError
 */
export interface ValidationError {
    /**
     * 
     * @type {string}
     * @memberof ValidationError
     */
    message?: string;
    /**
     * 
     * @type {{ [key: string]: Array<string>; }}
     * @memberof ValidationError
     */
    errors?: { [key: string]: Array<string>; };
}

/**
 * Check if a given object implements the ValidationError interface.
 */
export function instanceOfValidationError(value: object): boolean {
    let isInstance = true;

    return isInstance;
}

export function ValidationErrorFromJSON(json: any): ValidationError {
    return ValidationErrorFromJSONTyped(json, false);
}

export function ValidationErrorFromJSONTyped(json: any, ignoreDiscriminator: boolean): ValidationError {
    if ((json === undefined) || (json === null)) {
        return json;
    }
    return {
        
        'message': !exists(json, 'message') ? undefined : json['message'],
        'errors': !exists(json, 'errors') ? undefined : json['errors'],
    };
}

export function ValidationErrorToJSON(value?: ValidationError | null): any {
    if (value === undefined) {
        return undefined;
    }
    if (value === null) {
        return null;
    }
    return {
        
        'message': value.message,
        'errors': value.errors,
    };
}

