-- Insert test user (user_id = 1)
INSERT INTO `user` (`user_id`, `username`, `password`, `email`, `role`, `qr_code`, `lifetime_points`)
VALUES (1, 'testuser', '$2y$10$test_hash', 'test@example.com', 'user', 'QR_TEST_001', 0);

-- Insert test recycling bin (bin_id = 1)
INSERT INTO `recycling_bin` (`bin_id`, `bin_name`, `bin_location`)
VALUES (1, 'Main Bin', 'Campus Building A');
