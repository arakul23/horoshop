<?php

declare(strict_types=1);

namespace App\Tests\DTO;

use App\DTO\UserDto;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UserDtoValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testPhoneAcceptsOnlyDigits(): void
    {
        $dto = $this->createValidDto();
        $dto->phone = '12ab56';

        $violations = $this->validator->validate($dto);

        self::assertSame(1, $violations->count());
        self::assertSame('phone', $violations[0]->getPropertyPath());
        self::assertSame('Phone must contain only digits', $violations[0]->getMessage());
    }

    #[DataProvider('validPhoneProvider')]
    public function testPhonePassesValidationForDigitsOnly(string $phone): void
    {
        $dto = $this->createValidDto();
        $dto->phone = $phone;

        $violations = $this->validator->validate($dto);

        self::assertSame(0, $violations->count());
    }

    public static function validPhoneProvider(): array
    {
        return [
            ['1234'],
            ['12345678'],
        ];
    }

    private function createValidDto(): UserDto
    {
        $dto = new UserDto();
        $dto->login = 'user123';
        $dto->phone = '12345678';
        $dto->password = 'pass1234';

        return $dto;
    }
}
