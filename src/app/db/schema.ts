import { mysqlTable, serial, varchar, timestamp } from 'drizzle-orm/mysql-core';

export const members = mysqlTable('members', {
  id: serial('id').primaryKey(),
  name: varchar('name', { length: 255 }),
  email: varchar('email', { length: 255 }),
  membership_plan: varchar('membership_plan', { length: 100 }),
  start_date: timestamp('start_date'),
  end_date: timestamp('end_date'),
});
