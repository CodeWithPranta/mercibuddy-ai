<?php

test('calculated a discounted price', function () {
    // Arrange
    $price = 100;
    $discount = 20;

    // Act
    $result = $price - ($price * $discount / 100);

    // Assert
    expect($result)->toBe(80);
});
