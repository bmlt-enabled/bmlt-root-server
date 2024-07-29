import { writable } from 'svelte/store';

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
