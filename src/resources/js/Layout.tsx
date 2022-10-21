import { Container } from '@mui/material';
import { Navbar } from './sections/Navbar';
import { Header } from './sections/Header';

type Props = {
  children: JSX.Element;
};

export const Layout = ({ children }: Props) => {
  return (
    <>
      <Header />
      <Navbar />
      <Container maxWidth='lg'>{children}</Container>
    </>
  );
};
