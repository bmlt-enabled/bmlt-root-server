import { AppBar, Container, Typography } from '@mui/material';
import { styled } from '@mui/system';

const StyledAppBar = styled(AppBar)(({ theme }) => ({
  paddingTop: theme.spacing(2),
  paddingBottom: theme.spacing(2),
}));

export const Header = () => {
  return (
    <StyledAppBar position='relative' elevation={0}>
      <Container maxWidth='lg'>
        <Typography color='dark.main'>Root Server Header</Typography>
      </Container>
    </StyledAppBar>
  );
};
