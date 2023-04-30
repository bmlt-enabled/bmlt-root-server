type Props = {
  mode: string;
};

export function getMode() {
  const mode = localStorage.getItem('mode');
  console.log('mode2', mode)
  if (mode) {
    return mode;
  } else {
    return 'light';
  }
}

export function setMode(mode: string): void {
  localStorage.setItem('mode', mode);
}
