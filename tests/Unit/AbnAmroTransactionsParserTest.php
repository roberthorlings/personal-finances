<?php

namespace Tests\Unit;

use App\Model\Import\AbnAmroTransactionsParser;
use App\Model\Account;
use App\Model\Category;
use ArrayIterator;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AbnAmroTransactionsParserTest extends TestCase
{
    /**
     * @var AbnAmroTransactionsParser
     */
    private $parser;

    private $account;
    private $transferCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account  = new Account(["name" => "My account", "iban" => "NL00ABNA0123456789"]);
        $this->account->id = 4;

        $this->transferCategory = new Category(["name" => "Transfers"]);

        $this->parser = new AbnAmroTransactionsParser();
        $this->parser->setAccountsByAccountNo(Collection::make(["123456789" => $this->account]));
        $this->parser->setTransferCategory($this->transferCategory);
    }

    /**
     * Test basic parsing of CSV transaction line. This test verifies:
     *      - date parsing
     *      - amount parsing
     *      - account lookup
     * @return void
     */
    public function testParsing()
    {
        $lines = [
            ["123456789", "EUR", "20190122", "992,37", "791,25", "20190123", "-201,12", "Unknown description format"],
            ["000000000", "EUR", "20190122", "992,37", "791,25", "20190123", "-201,12", "Unknown description format"]
        ];

        $transactions = $this->parser->parse(new ArrayIterator($lines));

        // Expect first entry to be imported with the correct amount, date and account_id
        // Second entry is skipped due to unknown account
        $this->assertEquals(
            [[
                'amount' => -201.12,
                'account_id' => 4,
                'category_id' => null,
                'date' => \DateTime::createFromFormat( "Y-m-d", "2019-01-22"),
                'opposing_account_name' => '',
                'opposing_account_iban' => '',
                'description' => 'Unknown description format'
            ]],
            $transactions
        );
    }

    public function testTrtpParsing()
    {
        $lines = [
            $this->input("/TRTP/SEPA Incasso algemeen doorlopend/CSID/NL03ZZZ410423870000/NAME/TESTING OTHER ACCOUNT/MARF/CNLa0P1v00000pzM5zEAE/REMI/Thanks for the donation/IBAN/NL91INGB0000001111/BIC/INGBNL2A/EREF/1019081001588523"),
            $this->input("/TRTP/SEPA Incasso algemeen doorlopend/CSID/NL03ZZZ410423870000/NAME/TESTING OTHER ACCOUNT/MARF/CNLa0P1v00000pzM5zEAE/IBAN/NL91INGB0000001111/BIC/INGBNL2A/EREF/1019081001588523")
        ];

        $transactions = $this->parser->parse(new ArrayIterator($lines));

        $this->assertEquals(
            [
                $this->output([
                    'opposing_account_name' => 'TESTING OTHER ACCOUNT',
                    'opposing_account_iban' => 'NL91INGB0000001111',
                    'description' => 'Incasso algemeen doorlopend Thanks for the donation'
                ]),
                $this->output([
                    'opposing_account_name' => 'TESTING OTHER ACCOUNT',
                    'opposing_account_iban' => 'NL91INGB0000001111',
                    'description' => 'Incasso algemeen doorlopend TESTING OTHER ACCOUNT (1019081001588523)'
                ])
            ],
            $transactions
        );
    }

    public function testAbnAmroParsing()
    {
        $lines = [
            $this->input("ABN AMRO Bank N.V.               BetaalGemak E               3,40Betaalpas                   0,60")
        ];

        $transactions = $this->parser->parse(new ArrayIterator($lines));

        $this->assertEquals(
            [
                $this->output([
                    'opposing_account_name' => 'ABN AMRO',
                    'description' => 'BetaalGemak E               3,40Betaalpas                   0,60'
                ])
            ],
            $transactions
        );
    }

    public function testGeaBeaParsing()
    {
        $lines = [
            $this->input("GEA   NR:375228   01.22.19/09.50 Rabobank geldautomaat,PAS964"),
            $this->input("BEA   NR:31S5T6   01.25.19/10.24 ALBERT HEIJN 0000 UTRECHT,PAS964"),
        ];

        $transactions = $this->parser->parse(new ArrayIterator($lines));

        $this->assertEquals(
            [
                $this->output([
                    'opposing_account_name' => 'Rabobank geldautomaat',
                    'description' => 'GEA Rabobank geldautomaat'
                ]),
                $this->output([
                    'opposing_account_name' => 'ALBERT HEIJN 0000 UTRECHT',
                    'description' => 'ALBERT HEIJN 0000 UTRECHT'
                ])
            ],
            $transactions
        );
    }

    /**
     * Test basic parsing of SEPA descriptions
     * @return void
     */
    public function testSepaParsing()
    {
        $lines = [
            $this->input('SEPA iDEAL                       IBAN: NL61ABNA0082216113        BIC: ABNANL2A                    Naam: AAB RETAIL INZ TIKKIE     Omschrijving: 000112128315 00300 04183484482 Someone else NL11ABNA0314719593                      Kenmerk: 02-07-2019 22:56 0030004183484482'),
            $this->input('SEPA iDEAL                       IBAN: NL69ABNA0364352591        BIC: ABNANL2A                    Naam: DERDENGELDEN DIGIWALLET   Omschrijving: 182567613 00300036 83470690 UGA19 2501 Things 088293Long descr iption                         Kenmerk: 30-01-2019 15:49 003000'),
            $this->input('SEPA Incasso algemeen doorlopend Incassant: NL74ZZZ342764500017  Naam: SPOTIFY                    Machtiging: 4414839864571991    Omschrijving: SpotifyNL 00000000 4C                              IBAN: NL48ABNA0501234567         Kenmerk: C4715601048577278C     Voor: H DE VRIES CJ'),
            $this->input('SEPA Incasso algemeen doorlopend Incassant: NL70XS4332875340001  Naam: Provider                   Machtiging: M10010080140        Omschrijving: Factuur 10-07-2019 , klantnummer 123456                     IBAN: NL55INGB000654321        Kenmerk: 14250000059289'),
            $this->input('SEPA Overboeking                 IBAN: NL94INGB0001330007        BIC: INGBNL2A                    Naam: Johnson                Omschrijving: marktplaats boek'),
            $this->input('SEPA Unknown                 IBAN: NL94INGB0001330007        BIC: INGBNL2A                    Naam: Johnson                Omschrijving: marktplaats boek')
        ];

        $transactions = $this->parser->parse(new ArrayIterator($lines));

        $this->assertEquals(
            [
                $this->output([
                    'opposing_account_name' => 'AAB RETAIL INZ TIKKIE',
                    'opposing_account_iban' => 'NL61ABNA0082216113',
                    'description' => 'iDEAL 000112128315 00300 04183484482 Someone else NL11ABNA0314719593'
                ]),
                $this->output([
                    'opposing_account_name' => 'DERDENGELDEN DIGIWALLET',
                    'opposing_account_iban' => 'NL69ABNA0364352591',
                    'description' => 'iDEAL 182567613 00300036 83470690 UGA19 2501 Things 088293Long descr iption'
                ]),
                $this->output([
                    'opposing_account_name' => 'SPOTIFY',
                    'opposing_account_iban' => 'NL48ABNA0501234567',
                    'description' => 'Incasso algemeen doorlopend SpotifyNL 00000000 4C'
                ]),
                $this->output([
                    'opposing_account_name' => 'Provider',
                    'opposing_account_iban' => 'NL55INGB000654321',
                    'description' => 'Incasso algemeen doorlopend Factuur 10-07-2019 , klantnummer 123456'
                ]),
                $this->output([
                    'opposing_account_name' => 'Johnson',
                    'opposing_account_iban' => 'NL94INGB0001330007',
                    'description' => 'Overboeking marktplaats boek'
                ]),
                $this->output([
                    'opposing_account_name' => '',
                    'opposing_account_iban' => '',
                    'description' => 'SEPA Unknown                 IBAN: NL94INGB0001330007        BIC: INGBNL2A                    Naam: Johnson                Omschrijving: marktplaats boek'
                ]),
            ],
            $transactions
        );
    }

    private function input(string $description) {
        return ["123456789", "EUR", "20190122", "992,37", "991,37", "20190123", "-1,00", $description];
    }

    private function output(array $expected = []) {
        return array_merge(
            [
                'amount' => -1.0,
                'account_id' => 4,
                'category_id' => null,
                'date' => \DateTime::createFromFormat( "Y-m-d", "2019-01-22"),
                'opposing_account_name' => '',
                'opposing_account_iban' => '',
            ],
            $expected
        );
    }

}
