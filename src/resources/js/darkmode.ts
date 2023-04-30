export function getMode() {
  const mode = localStorage.getItem('mode');
  if (mode) {
    return mode;
  } else {
    return 'light';
  }
}

export function setMode(mode: string): void {
  localStorage.setItem('mode', mode);
}
