import { Dashboard } from '../pages/Dashboard';
import { Formats } from '../pages/Formats';
import { Meetings } from '../pages/Meetings';
import { MyAccount } from '../pages/MyAccount';
import { ServiceBodies } from '../pages/ServiceBodies';
import { Users } from '../pages/Users';

export const routes = [
  {
    path: '/',
    component: Dashboard,
  },
  {
    path: '/meetings',
    component: Meetings,
  },
  {
    path: '/service-bodies',
    component: ServiceBodies,
  },
  {
    path: '/users',
    component: Users,
  },
  {
    path: '/formats',
    component: Formats,
  },
  {
    path: '/my-account',
    component: MyAccount,
  },
];
