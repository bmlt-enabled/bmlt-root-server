import { AppBar, Box, Container, Typography } from '@mui/material';
import { styled } from '@mui/system';

import { strings } from '../localization';
import { DarkmodeSelect } from '../partials/utilities/DarkmodeSelect';
import { GithubButton } from '../partials/utilities/GithubButton';
import { LanguageSelect } from '../partials/utilities/LanguageSelect';

const StyledAppBar = styled(AppBar)(({ theme }) => ({
  paddingTop: theme.spacing(2),
  paddingBottom: theme.spacing(2),
  backgroundColor: theme.palette.primary.main,
}));

const StyledHeaderContainer = styled(Container)({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
});

const StyledBox = styled(Box)({
  display: 'flex',
  alignItems: 'center',
});

export const Header = () => {
  const languageSelectorBox = settings.isLanguageSelectorEnabled ? (
    <Box>
      <LanguageSelect />
    </Box>
  ) : (
    ''
  );
  return (
    <StyledAppBar position='relative' elevation={0}>
      <StyledHeaderContainer maxWidth='lg'>
        <Typography variant='h1' color='dark.main'>
          {strings.rootServerTitle}
        </Typography>
        <StyledBox>
          {languageSelectorBox}
          <DarkmodeSelect />
          <GithubButton />
        </StyledBox>
      </StyledHeaderContainer>
    </StyledAppBar>
  );
};
