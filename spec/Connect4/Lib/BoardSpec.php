<?php

namespace spec\Connect4\Lib;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Connect4\Lib\Player;
use Connect4\Lib\GameState;
use Connect4\Lib\Column;
use Connect4\Lib\Row;
use Connect4\Lib\Move;

class BoardSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(new Player(Player::RED));
    }
    
    function it_is_initializable()
    {
        $this->shouldHaveType('Connect4\Lib\Board');
    }
    
    function it_returns_an_initial_state()
    {
        $this->getState()->shouldBeLike(new GameState(GameState::RED_PLAYS_NEXT));
    }
    
    function it_knows_the_next_player()
    {
        $this->getNextPlayer()->shouldBeLike(new Player(Player::RED));
    }
    
    function it_is_initially_empty()
    {
        foreach (range(1, 7) as $columnNumber) {
            $column = new Column($columnNumber);
            
            foreach (range(1, 6) as $rowNumber) {
                $row = new Row($rowNumber);
                
                $this->getContentsOfCell($column, $row)->shouldBe(null);
            }
        }
    }
    
    function it_can_get_the_contents_of_a_column()
    {
        $this->getColumnContents(new Column(4))->shouldBe([null, null, null, null, null, null]);
    }
    
    function it_rejects_a_move_for_the_wrong_player()
    {
        $column = new Column(4);
        $player = new Player(Player::YELLOW);
        $move = new Move($player, $column);
        
        $this->shouldThrow('Connect4\Lib\Exception\WrongPlayerException')->during('applyMove', [$move]);
    }
    
    function it_applies_two_moves_in_the_same_column()
    {
        $firstMove = $this->getMove(Player::RED, 4);
        $boardAfterMoveOne = $this->applyMove($firstMove);
        $boardAfterMoveOne->getContentsOfCell(new Column(4), new Row(1))->shouldBe($firstMove->getPlayer());
        
        $secondMove = $this->getMove(Player::YELLOW, 4);
        $boardAfterMoveTwo = $boardAfterMoveOne->applyMove($secondMove);
        $boardAfterMoveTwo->getContentsOfCell(new Column(4), new Row(1))->shouldBe($firstMove->getPlayer());
        $boardAfterMoveTwo->getContentsOfCell(new Column(4), new Row(2))->shouldBe($secondMove->getPlayer());
        $boardAfterMoveTwo->getContentsOfCell(new Column(3), new Row(1))->shouldBe(null);
    }
    
    function it_rejects_a_move_in_a_full_column()
    {
        $redMove = $this->getMove(Player::RED, 3);
        $yellowMove = $this->getMove(Player::YELLOW, 3);
        
        $board = $this->applyMove($redMove);
        $board = $board->applyMove($yellowMove);
        $board = $board->applyMove($redMove);
        $board = $board->applyMove($yellowMove);
        $board = $board->applyMove($redMove);
        $board = $board->applyMove($yellowMove);
        
        $board->shouldThrow('Connect4\Lib\Exception\ColumnFullException')->during('applyMove', [$redMove]);
    }
    
    function it_applies_two_moves_in_different_columns()
    {
        $firstMove = $this->getMove(Player::RED, 4);
        $boardAfterMoveOne = $this->applyMove($firstMove);
        $boardAfterMoveOne->getContentsOfCell(new Column(4), new Row(1))->shouldBe($firstMove->getPlayer());
        
        $secondMove = $this->getMove(Player::YELLOW, 3);
        $boardAfterMoveTwo = $boardAfterMoveOne->applyMove($secondMove);
        $boardAfterMoveTwo->getContentsOfCell(new Column(4), new Row(1))->shouldBe($firstMove->getPlayer());
        $boardAfterMoveTwo->getContentsOfCell(new Column(4), new Row(2))->shouldBe(null);
        $boardAfterMoveTwo->getContentsOfCell(new Column(3), new Row(1))->shouldBe($secondMove->getPlayer());
    }
    
    /**
     * @param string $playerColour
     * @param integer $columnNumber
     * @return Move
     */
    private function getMove($playerColour, $columnNumber)
    {
        return new Move(new Player($playerColour), new Column($columnNumber));
    }
}