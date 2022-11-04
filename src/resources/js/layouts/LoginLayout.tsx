import { Container, Box, styled } from '@mui/system';
import { Header } from '../sections/Header';

type Props = {
  children: React.ReactNode;
};

const StyledLayoutWrapper = styled(Box)({
  minHeight: '100vh',
  display: 'flex',
  flexDirection: 'column',
});

const StyledContainer = styled(Container)(({ theme }) => ({
  marginTop: theme.spacing(6),
}));

export const LoginLayout = ({ children }: Props) => {
  return (
    <StyledLayoutWrapper>
      <Header />
      <StyledContainer maxWidth='md'>{children}</StyledContainer>
    </StyledLayoutWrapper>
  );
};
