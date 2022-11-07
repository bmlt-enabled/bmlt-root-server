import { AppBar, Container, Typography } from '@mui/material';
import { styled } from '@mui/system';

import { strings } from '../localization';
import { UtilityBar } from './UtilityBar';

const StyledAppBar = styled(AppBar)(({ theme }) => ({
  paddingTop: theme.spacing(2),
  paddingBottom: theme.spacing(2),
}));

export const Header = () => {
  console.log('strings', strings);
  return (
    <StyledAppBar position='relative' elevation={0}>
      <UtilityBar />
      <Container maxWidth='lg'>
        <Typography color='dark.main'>{strings.rootServerTitle}</Typography>
      </Container>
    </StyledAppBar>
  );
};
