import { render } from '@testing-library/svelte';
import App from '../App.svelte';

test('renders the app component', () => {
  const { getByText } = render(App);

  // Check for the BmltServer component
  expect(getByText('BMLT Root Server')).toBeInTheDocument();

  // Check for the presence of the <title> element
  expect(document.title).toBe('BMLT Root Server');
});
