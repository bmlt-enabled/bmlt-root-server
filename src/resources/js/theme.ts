import { PaletteMode } from '@mui/material';

// declare module '@mui/material/styles' {
//   interface Theme {
//     white: {
//       main: string;
//     };
//   }
//   // allow configuration using `createTheme`
//   interface ThemeOptions {
//     white?: {
//       main?: string;
//     };
//   }
// }

// type Props = {
//   mode: string;
// };
const colors = {
  white: '#fff',
  light: {
    primary: '#4a90e2',
    secondary: '#E460AB',
    success: '#678d06',
    error: '#ff3333',
    // background: '#fff',
  },
  dark: {
    primary: '#333',
    secondary: '#555',
    success: '#678d06',
    error: '#ff3333',
    // background: '#000',
  },
};
export const getDesignTokens = (mode: PaletteMode) => ({
  palette: {
    white: {
      main: colors.white,
    },
    mode,
    ...(mode === 'light'
      ? {
          primary: {
            main: colors.light.primary,
          },
          secondary: {
            main: colors.light.secondary,
          },
          success: {
            main: colors.light.success,
            contrastText: '#fff',
          },
          error: {
            main: colors.light.error,
          },
          // background: {
          //   default: colors.light.background,
          // },
        }
      : {
          primary: {
            main: colors.dark.primary,
          },
          secondary: {
            main: colors.dark.secondary,
          },
          success: {
            main: colors.dark.success,
            contrastText: '#fff',
          },
          error: {
            main: colors.dark.error,
          },
          // background: {
          //   main: colors.dark.background,
          // },
        }),
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
});

export const getThemedComponents = (mode: PaletteMode) => ({
  components: {
    ...(mode === 'light'
      ? {
          MuiFormLabel: {
            styleOverrides: {
              asterisk: {
                color: colors.light.primary,
                '&$error': {
                  color: colors.light.error,
                },
              },
            },
          },
          MuiFormHelperText: {
            styleOverrides: {
              root: {
                color: colors.light.error,
                position: 'absolute',
                bottom: '-1.25rem',
                left: '0',
              },
            },
          },
        }
      : {
          MuiFormLabel: {
            styleOverrides: {
              asterisk: {
                color: colors.dark.primary,
                '&$error': {
                  color: colors.dark.error,
                },
              },
            },
          },
          MuiFormHelperText: {
            styleOverrides: {
              root: {
                color: colors.dark.error,
                position: 'absolute',
                bottom: '-1.25rem',
                left: '0',
              },
            },
          },
        }),
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
