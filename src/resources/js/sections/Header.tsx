import { AppBar, Box, Container, Typography } from '@mui/material';
import { styled } from '@mui/system';

import { strings } from '../localization';
import { LanguageSelect } from '../partials/utilities/LanguageSelect';

const StyledAppBar = styled(AppBar)(({ theme }) => ({
  paddingTop: theme.spacing(2),
  paddingBottom: theme.spacing(2),
}));

const StyledHeaderContainer = styled(Container)({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
});

export const Header = () => {
  console.log('strings', strings);
  return (
    <StyledAppBar position='relative' elevation={0}>
      <StyledHeaderContainer maxWidth='lg'>
        <Typography variant='h1' color='dark.main'>
          {strings.rootServerTitle}
        </Typography>
        <Box>
          <Typography color='white'>Select Language</Typography>
          <LanguageSelect />
        </Box>
      </StyledHeaderContainer>
    </StyledAppBar>
  );
};
