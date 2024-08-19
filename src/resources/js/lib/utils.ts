import { writable } from 'svelte/store';
import { translations } from '../stores/localization';

export const isDirty = writable(false);

export function formIsDirty(initialValues: any, currentValues: any): boolean {
  for (const key of Object.keys(initialValues)) {
    if (!Object.prototype.hasOwnProperty.call(currentValues, key)) {
      // If the currentData just doesn't have the field, ignore. This happens, for example,
      // in the UserForm's password field.
      continue;
    }
    if (Array.isArray(initialValues[key])) {
      const arr1 = [...initialValues[key]].sort();
      const arr2 = [...currentValues[key]].sort();
      if (arr1.length !== arr2.length) {
        isDirty.set(true);
        return true;
      }
      for (const i in arr1) {
        if (arr1[i] !== arr2[i]) {
          isDirty.set(true);
          return true;
        }
      }
    } else if (initialValues[key] !== currentValues[key]) {
      isDirty.set(true);
      return true;
    }
  }
  isDirty.set(false);
  return false;
}

export function convertTo12Hour(time: string): string {
  const [hour, minute] = time.split(':');
  let hourNum = parseInt(hour, 10);
  const ampm = hourNum >= 12 ? translations.getString('postMeridiem') : translations.getString('anteMeridiem');

  if (hourNum > 12) {
    hourNum -= 12;
  } else if (hourNum === 0) {
    hourNum = 12;
  }

  return `${hourNum.toString().padStart(2, '0')}:${minute} ${ampm}`;
}

export function is24hrTime() {
  const date = new Date();
  const timeString = date.toLocaleTimeString();
  return !timeString.includes('AM') && !timeString.includes('PM');
}
