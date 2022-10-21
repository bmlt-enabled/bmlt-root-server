import { createTheme } from '@mui/material/styles';

export const theme = createTheme({
  palette: {
    defaultText: {
      main: '#000000',
    },
    dark: {
      main: '#232424',
    },
    primary: {
      main: '#4a90e2',
    },
    secondary: {
      main: '#E460AB',
    },
    tertiary: {
      main: '#d8d8d8',
    },
    white: {
      main: '#fff',
      contrastText: '#000',
    },
    spark: {
      main: '#b7332b',
      contrastText: '#fff',
    },
    leaf: {
      main: '#9eab3f',
      contrastText: '#fff',
    },
    scout: {
      main: '#00b0c0',
      contrastText: '#fff',
    },
    tinker: {
      main: '#01acab',
      contrastText: '#ffcc00',
    },
    muted: {
      main: '#444',
    },
    lightGray: {
      main: '#f6f6f6',
      contrastText: '#000',
    },
    darkGray: {
      main: '#181818',
      contrastText: '#fff',
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
    p: {
      fontSize: '1rem',
    },
  },
  borderRadius: '10px',
  menuWidth: '18.75rem',
  collapsedMenuWidth: '4rem',
  headerHeight: '107px',
  footerHeight: '40px',
  spacer: '1rem',
  borders: {
    radius: {
      default: '10px',
      sm: '4px',
      md: '8px',
      lg: '16px',
      none: '0',
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
