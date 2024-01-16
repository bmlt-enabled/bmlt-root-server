import { Box, Typography } from '@mui/material';
import { styled } from '@mui/system';
import { ReactNode } from 'react';

const StyledWrapper = styled(Box)(({ theme }) => ({
  width: '100%',
  padding: theme.spacing(4),
  border: '1px solid #ccc',
  borderRadius: theme.shape.borderRadius,
}));

const StyledFormLabel = styled(Typography)(({ theme }) => ({
  marginBottom: theme.spacing(6),
  color: theme.palette.primary.main,
  textAlign: 'center',
}));

type Props = {
  heading: string | null;
  children: ReactNode;
};
const FormWrapper = ({ children, heading }: Props) => {
  return (
    <StyledWrapper>
      <StyledFormLabel variant='h3'>{heading}</StyledFormLabel>
      {children}
    </StyledWrapper>
  );
};

export default FormWrapper;
