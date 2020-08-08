<?php

return [
    'max_try_time' => 10, // Nếu bot gặp lỗi, tăng count_error lên, nếu vượt quá max_try_time thì ko add vào queue nữa
    'try_news_feed_after' => 5, // Nếu news feed ko có bài nào hoặc có nhưng tương tác hết rồi, thì chờ lâu lâu sau quét lại
];
