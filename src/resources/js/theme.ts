import { createTheme } from '@mui/material/styles';

export const theme = createTheme({
  palette: {
    primary: {
      main: '#4a90e2',
    },
    secondary: {
      main: '#E460AB',
    },
    success: {
      main: '#678d06',
      contrastText: '#fff',
    },
    error: {
      main: '#c6203e',
      contrastText: '#fff',
    },
  },
  typography: {
    fontFamily: 'Montserrat',

    h1: {
      fontSize: '2.5rem',
      fontWeight: '800',
    },
    h2: {
      fontSize: '1.75rem',
      fontWeight: '800',
    },
    h3: {
      fontSize: '1.5rem',
    },
    h4: {
      fontSize: '1.25rem',
    },

    h5: {
      fontSize: '1rem',
    },
    h6: {
      fontSize: '0.875rem',
    },
  },
  components: {
    MuiButton: {
      styleOverrides: {
        root: {
          padding: '0.5rem 1.5rem',
        },
      },
      variants: [
        {
          props: {
            size: 'large',
          },
          style: {
            height: '50px',
            width: '250px',
            fontWeight: '600',
            fontSize: '0.75rem',
            lineHeight: '1.5',
          },
        },
      ],
    },
    MuiDivider: {
      styleOverrides: {
        root: {
          margin: '1rem 0',
        },
      },
    },
  },
});
